<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlaRequest;
use App\Http\Requests\SlaSampleRequest;
use App\Http\Resources\SLA\SLAResource;
use App\Http\Resources\SLA\SLASampleResource;
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
                'status' => true,
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
     * Menampilkan Sample SLA (untuk audit dan verifikasi SLA)
     */
    public function samples(SlaSampleRequest $request): JsonResponse
    {
        try {
            $data = $this->service->getSamples($request->validated());

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Berhasil mendapatkan data sample SLA',
                'data' => SLASampleResource::collection(collect($data['list']))->resolve(),
                'meta' => $data['meta'],
            ]);
        } catch (\Throwable $e) {
            Log::error('[SLAController@samples] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mendapatkan data sample SLA', 500, ['error' => $e->getMessage()]);
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

    /**
     * Update Target SLA Operator
     */
    public function updateSlaTarget(\App\Http\Requests\Operator\UpdateSlaTargetRequest $request): JsonResponse
    {
        try {
            $id = $request->attributes->get('auth_user_id');
            $data = $this->service->updateSlaTarget($id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Target SLA operator berhasil diperbarui.',
                'data' => $data,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Operator tidak ditemukan'], 404);
        } catch (\Throwable $e) {
            Log::error('[SLAController@updateSlaTarget] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui target SLA operator', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Target SLA Operator
     */
    public function getSlaTarget(): JsonResponse
    {
        try {
            $id = request()->attributes->get('auth_user_id');
            $data = $this->service->getSlaTarget($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengambil data target SLA operator',
                'data' => $data,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Operator tidak ditemukan'], 404);
        } catch (\Throwable $e) {
            Log::error('[SLAController@getSlaTarget] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal mengambil target SLA operator', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get User SLA configuration settings
     */
    public function getSettings(): JsonResponse
    {
        try {
            $userId = request()->attributes->get('auth_user_id') ?: (auth()->user()?->id);
            if (!$userId) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }
            $data = $this->service->getUserSettings($userId);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengambil konfigurasi status SLA',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('[SLAController@getSettings] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal mengambil konfigurasi status SLA', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update User SLA configuration settings
     */
    public function updateSettings(\App\Http\Requests\UpdateSlaSettingsRequest $request): JsonResponse
    {
        try {
            $userId = $request->attributes->get('auth_user_id') ?: (auth()->user()?->id);
            if (!$userId) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }
            $data = $this->service->updateUserSettings($userId, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Konfigurasi status SLA berhasil diperbarui dan disinkronkan.',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('[SLAController@updateSettings] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui konfigurasi status SLA', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get target SLA for a specific ajuan
     */
    public function getAjuanTarget(string|int $ajuan_id): JsonResponse
    {
        try {
            $data = $this->service->getAjuanTarget($ajuan_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengambil target SLA ajuan',
                'data' => $data,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            Log::error('[SLAController@getAjuanTarget] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal mengambil target SLA ajuan', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update target SLA for a specific ajuan
     */
    public function updateAjuanTarget(string|int $ajuan_id, \App\Http\Requests\UpdateAjuanSlaTargetRequest $request): JsonResponse
    {
        try {
            $data = $this->service->updateAjuanTarget($ajuan_id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Target SLA ajuan berhasil diperbarui.',
                'data' => $data,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            Log::error('[SLAController@updateAjuanTarget] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui target SLA ajuan', 'error' => $e->getMessage()], 500);
        }
    }
}