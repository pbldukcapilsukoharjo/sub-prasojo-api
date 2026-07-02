<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\OperatorFilterRequest;
use App\Filters\OperatorFilter;
use App\Services\OperatorService;
use App\Exports\OperatorRankingExport;
use App\Http\Resources\Operator\OperatorKpiGlobalResource;
use App\Http\Resources\Operator\OperatorPeringkatResource;
use App\Http\Resources\Operator\OperatorKpiDetailResource;
use App\Http\Resources\Operator\OperatorRiwayatResource;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class OperatorController extends Controller
{
    public function __construct(
        private OperatorService $service
    ) {}

    /**
     * KPI Global Operator
     */
    public function kpiGlobal(OperatorFilterRequest $request): JsonResponse
    {
        $filter = new OperatorFilter($request->validated());
        $data = $this->service->getKpiGlobal($filter);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil KPI global operator',
            'data' => new OperatorKpiGlobalResource($data),
        ]);
    }

    /**
     * List Peringkat Operator
     */
    public function peringkat(OperatorFilterRequest $request): JsonResponse
    {
        $filter = new OperatorFilter($request->validated());
        $limit = (int) $request->input('limit', 10);
        $data = $this->service->getRanking($filter, $limit);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan data operator',
            'data' => new OperatorPeringkatResource($data),
        ]);
    }

    /**
     * Detail Operator (KPI & Chart)
     */
    public function kpi(OperatorFilterRequest $request, int $id): JsonResponse
    {
        $data = $this->service->getDetailKpi($id, $request->validated());

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Detail operator berhasil ditemukan',
            'data' => new OperatorKpiDetailResource($data),
        ]);
    }

    /**
     * Detail Operator (Riwayat Layanan)
     */
    public function riwayat(OperatorFilterRequest $request, int $id): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);
        $data = $this->service->getRiwayat($id, $request->validated(), $limit);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Riwayat operator berhasil ditemukan',
            'data' => new OperatorRiwayatResource($data),
        ]);
    }

    /**
     * Export Ranking Operator
     */
    public function export(OperatorFilterRequest $request): BinaryFileResponse
    {
        $filter = new OperatorFilter($request->validated());
        $filename = 'export_operator_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new OperatorRankingExport($filter), $filename);
    }
}
