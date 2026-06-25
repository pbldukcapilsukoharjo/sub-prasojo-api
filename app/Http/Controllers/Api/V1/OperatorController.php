<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Filters\OperatorFilter;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\OperatorService;
use App\Exports\OperatorRankingExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OperatorController extends Controller
{
    protected OperatorService $operatorService;

    public function __construct(OperatorService $operatorService)
    {
        $this->operatorService = $operatorService;
    }

    public function kpiGlobal(Request $request)
    {
        $filter = new OperatorFilter($request->all());
        $data = $this->operatorService->getKpiGlobal($filter);

        return ApiResponse::success('Berhasil', $data);
    }

    public function ranking(Request $request)
    {
        $filter = new OperatorFilter($request->all());
        $perPage = (int) $request->input('per_page', 10);
        
        $paginator = $this->operatorService->getRanking($filter, $perPage);

        $mappedData = collect($paginator->items())->map(function ($item, $key) use ($paginator) {
            $index = ($paginator->currentPage() - 1) * $paginator->perPage() + $key + 1;
            return [
                'peringkat' => $index,
                'id_operator' => $item->id_operator,
                'nama' => $item->nama,
                'total_berkas' => (int) $item->total_berkas,
                'rata_rata_waktu_menit' => round((float) $item->rata_rata_waktu_menit, 2),
            ];
        })->toArray();

        // Using setCollection to override the items inside the paginator before returning
        $paginator->setCollection(collect($mappedData));

        return ApiResponse::paginated('Berhasil', $paginator);
    }

    public function detail(int $id_operator)
    {
        try {
            $data = $this->operatorService->getDetail($id_operator);
            return ApiResponse::success('Berhasil', $data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Operator tidak ditemukan', 404);
        }
    }

    public function exportRanking(Request $request)
    {
        $filter = new OperatorFilter($request->all());
        $date = date('Ymd_His');
        $filename = "export_operator_ranking_{$date}.xlsx";

        return Excel::download(new OperatorRankingExport($filter), $filename);
    }
}
