<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PengajuanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Ajuan\IndexAjuanRequest;
use App\Http\Requests\LembarKerja\IndexLembarKerjaRequest;
use App\Http\Requests\Produk\IndexProdukRequest;
use App\Http\Requests\Pengajuan\ExportPengajuanRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;

final class PengajuanController extends Controller
{
    public function __construct(
        protected PengajuanService $service
    ) {}

    public function getLembarKerja(IndexLembarKerjaRequest $request): JsonResponse
    {
        try {
            $result = $this->service->getLembarKerjaList($request->validated());
            
            return ApiResponse::paginated(
                'Berhasil mengambil data lembar kerja', 
                $result['paginator'],
                [
                    'chart_status' => $result['chart_status'],
                    'chart_layanan' => $result['chart_layanan']
                ]
            );
        } catch (\Throwable $e) {
            Log::error('[PengajuanController@getLembarKerja] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil data lembar kerja', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getAjuan(IndexAjuanRequest $request): JsonResponse
    {
        try {
            $data = $this->service->getAjuanList($request->validated());
            return ApiResponse::paginated('Berhasil mengambil data ajuan', $data);
        } catch (\Throwable $e) {
            Log::error('[PengajuanController@getAjuan] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil data ajuan', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getProduk(IndexProdukRequest $request): JsonResponse
    {
        try {
            $data = $this->service->getProdukList($request->validated());
            return ApiResponse::paginated('Berhasil mengambil data produk', $data);
        } catch (\Throwable $e) {
            Log::error('[PengajuanController@getProduk] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil data produk', 500, ['error' => $e->getMessage()]);
        }
    }

    public function getDetailTimeline(Request $request, int $ajuan_id): JsonResponse
    {
        try {
            $data = $this->service->getDetailTimeline($ajuan_id);
            return ApiResponse::success('Berhasil mengambil detail timeline pengajuan', $data);
        } catch (\Throwable $e) {
            Log::error('[PengajuanController@getDetailTimeline] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil detail timeline pengajuan', 500, ['error' => $e->getMessage()]);
        }
    }

    public function export(ExportPengajuanRequest $request)
    {
        try {
            return $this->service->exportExcel($request->validated());
        } catch (\Throwable $e) {
            Log::error('[PengajuanController@export] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengekspor data pengajuan', 500, ['error' => $e->getMessage()]);
        }
    }
}
