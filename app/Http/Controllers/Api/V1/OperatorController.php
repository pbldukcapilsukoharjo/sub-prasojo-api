<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Filters\OperatorFilter;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\OperatorService;
use App\Exports\OperatorRankingExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

final class OperatorController extends Controller
{
    public function __construct(
        private readonly OperatorService $operatorService
    ) {}

    public function kpiGlobal(Request $request): JsonResponse
    {
        $filter = new OperatorFilter($request->all());
        $data = $this->operatorService->getKpiGlobal($filter);

        return ApiResponse::success('Berhasil', $data);
    }

    public function peringkat(Request $request): JsonResponse
    {
        $filter = new OperatorFilter($request->all());
        $perPage = (int) $request->input('limit', 10);
        
        $paginator = $this->operatorService->getRanking($filter, $perPage);

        $mappedData = collect($paginator->items())->map(function ($item, $key) use ($paginator) {
            $index = ($paginator->currentPage() - 1) * $paginator->perPage() + $key + 1;
            return [
                'id' => $item->id_operator,
                'peringkat' => $index,
                'operator' => $item->nama,
                'desa' => $item->desa ?? '-',
                'kecamatan' => collect(explode('|', $item->kecamatan_nama))->first() ?? '-',
                'jumlah_ajuan' => (int) $item->total_berkas,
                // keep rata_rata_waktu_menit if needed by frontend but openapi only specifies the above
                'rata_rata_waktu_menit' => round((float) $item->rata_rata_waktu_menit, 2),
            ];
        })->toArray();

        $paginator->setCollection(collect($mappedData));

        return ApiResponse::paginated('Berhasil', $paginator);
    }

    public function kpi(int $id, Request $request): JsonResponse
    {
        try {
            $data = $this->operatorService->getDetailKpi($id, $request->all());
            return ApiResponse::success('Detail operator berhasil ditemukan', $data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Operator tidak ditemukan', 404);
        }
    }

    public function riwayat(int $id, Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('limit', 10);
            $paginator = $this->operatorService->getDetailRiwayat($id, $request->all(), $perPage);
            return ApiResponse::paginated('Riwayat operator berhasil ditemukan', $paginator);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Operator tidak ditemukan', 404);
        }
    }

    public function exportRanking(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filter = new OperatorFilter($request->all());
        $date = date('Ymd_His');
        $filename = "export_operator_ranking_{$date}.xlsx";

        return Excel::download(new OperatorRankingExport($filter), $filename);
    }
}
