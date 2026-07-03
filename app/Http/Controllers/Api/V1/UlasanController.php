<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exports\UlasanExport;
use App\Filters\UlasanFilter;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\UlasanService;
use App\Http\Requests\Ulasan\UlasanRequest;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

final class UlasanController extends Controller
{
    public function __construct(
        private readonly UlasanService $ulasanService
    ) {}

    public function kpi(UlasanRequest $request): JsonResponse
    {
        try {
            $filter = new UlasanFilter($request->validated());
            $data = $this->ulasanService->getKpi($filter);

            return ApiResponse::success('Berhasil', $data);
        } catch (\Throwable $e) {
            Log::error('[UlasanController@kpi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil KPI ulasan', 500, ['error' => $e->getMessage()]);
        }
    }

    public function index(UlasanRequest $request): JsonResponse
    {
        try {
            $filter = new UlasanFilter($request->validated());
            $data = $this->ulasanService->getList($filter);

            return ApiResponse::paginated('Berhasil', $data);
        } catch (\Throwable $e) {
            Log::error('[UlasanController@index] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil daftar ulasan', 500, ['error' => $e->getMessage()]);
        }
    }

    public function export()
    {
        try {
            $data = $this->ulasanService->getForExport();

            $filename = 'export_ulasan_' . date('Ymd_His') . '.xlsx';

            return Excel::download(new UlasanExport($data), $filename);
        } catch (\Throwable $e) {
            Log::error('[UlasanController@export] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengekspor data ulasan', 500, ['error' => $e->getMessage()]);
        }
    }
}