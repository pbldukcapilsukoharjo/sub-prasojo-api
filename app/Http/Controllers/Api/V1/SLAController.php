<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlaRequest;
use App\Http\Resources\SLA\SLAResource;
use App\Exports\SlaLayananExport;
use App\Services\SLAService;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Responses\ApiResponse;

class SLAController extends Controller
{
    public function __construct(
        private SLAService $service
    ) {
    }

    /**
     * Menampilkan daftar SLA (beserta KPI global)
     */
    public function index(SlaRequest $request): JsonResponse 
    {
        try {
            $data = $this->service->index($request->validated());

            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'Berhasil mendapatkan data SLA',
                // Kita langsung pisahkan list (item) ke dalam 'data' dan informasi halaman ke dalam 'meta'
                'data' => \App\Http\Resources\SLA\SLADetailResource::collection(collect($data['list']))->resolve(),
                'meta' => $data['meta'],
            ]);
        } catch (\Throwable $e) {
            Log::error('[SLAController@index] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mendapatkan data SLA', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Menampilkan KPI Global SLA
     */
    public function kpi(SlaRequest $request): JsonResponse 
    {
        try {
            $data = $this->service->getKpi($request->validated());

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Berhasil',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('[SLAController@kpi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mendapatkan KPI SLA', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Export Excel data SLA
     */
    public function export(SlaRequest $request)
    {
        try {
            $data = $this->service->export($request->validated());
            
            return Excel::download(
                new SlaLayananExport($data), 
                'sla-layanan-' . date('Y-m-d-His') . '.xlsx'
            );
        } catch (\Throwable $e) {
            Log::error('[SLAController@export] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengekspor data SLA', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Trigger SLA recalculation manually.
     */
    public function recalculate(): JsonResponse
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('sla:recalculate');
            $output = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'status' => 'success',
                'message' => 'Proses kalkulasi ulang SLA berhasil dijalankan.',
                'data' => [
                    'output' => trim($output),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[SLAController@recalculate] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal menjalankan kalkulasi ulang SLA', 500, ['error' => $e->getMessage()]);
        }
    }
}