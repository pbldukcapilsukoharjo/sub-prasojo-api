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
use Illuminate\Support\Facades\Log;
use App\Http\Responses\ApiResponse;

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
        try {
            $filter = new OperatorFilter($request->validated());
            $data = $this->service->getKpiGlobal($filter);

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Berhasil mengambil KPI global operator',
                'data' => new OperatorKpiGlobalResource($data),
            ]);
        } catch (\Throwable $e) {
            Log::error('[OperatorController@kpiGlobal] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil KPI global operator', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * List Peringkat Operator
     */
    public function peringkat(OperatorFilterRequest $request): JsonResponse
    {
        try {
            $filter = new OperatorFilter($request->validated());
            $limit = (int) $request->input('limit', 10);
            $data = $this->service->getRanking($filter, $limit);

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Berhasil mendapatkan data operator',
                'data' => new OperatorPeringkatResource($data),
            ]);
        } catch (\Throwable $e) {
            Log::error('[OperatorController@peringkat] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mendapatkan peringkat operator', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Detail Operator (KPI & Chart)
     */
    public function kpi(OperatorFilterRequest $request, int $id): JsonResponse
    {
        try {
            $data = $this->service->getDetailKpi($id, $request->validated());

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Detail operator berhasil ditemukan',
                'data' => new OperatorKpiDetailResource($data),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Operator tidak ditemukan', 404);
        } catch (\Throwable $e) {
            Log::error('[OperatorController@kpi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil detail operator', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Detail Operator (Riwayat Layanan)
     */
    public function riwayat(OperatorFilterRequest $request, int $id): JsonResponse
    {
        try {
            $limit = (int) $request->input('limit', 10);
            $data = $this->service->getRiwayat($id, $request->validated(), $limit);

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Riwayat operator berhasil ditemukan',
                'data' => new OperatorRiwayatResource($data),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Operator tidak ditemukan', 404);
        } catch (\Throwable $e) {
            Log::error('[OperatorController@riwayat] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil riwayat operator', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Export Ranking Operator
     */
    public function export(OperatorFilterRequest $request)
    {
        try {
            $filter = new OperatorFilter($request->validated());
            $filename = 'export_operator_' . date('Ymd_His') . '.xlsx';
            return Excel::download(new OperatorRankingExport($filter), $filename);
        } catch (\Throwable $e) {
            Log::error('[OperatorController@export] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengekspor data operator', 500, ['error' => $e->getMessage()]);
        }
    }
}
