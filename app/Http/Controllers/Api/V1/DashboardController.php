<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Filters\DashboardFilter;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function kpi(Request $request): JsonResponse
    {
        try {
            $filter = new DashboardFilter($request->all());
            $data = $this->dashboardService->getKpi($filter);

            return ApiResponse::success('Berhasil mengambil KPI Dashboard', $data);
        } catch (\Throwable $e) {
            Log::error('[DashboardController@kpi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil KPI Dashboard', 500, ['error' => $e->getMessage()]);
        }
    }

    public function chartTrend(Request $request): JsonResponse
    {
        try {
            $filter = new DashboardFilter($request->all());
            $data = $this->dashboardService->getChartTrend($filter);

            return ApiResponse::success('Berhasil mengambil Chart Trend', $data);
        } catch (\Throwable $e) {
            Log::error('[DashboardController@chartTrend] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil Chart Trend', 500, ['error' => $e->getMessage()]);
        }
    }

    public function topWilayah(Request $request): JsonResponse
    {
        try {
            $filter = new DashboardFilter($request->all());
            $data = $this->dashboardService->getTopWilayah($filter);

            return ApiResponse::success('Berhasil mengambil Top Wilayah', $data);
        } catch (\Throwable $e) {
            Log::error('[DashboardController@topWilayah] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil Top Wilayah', 500, ['error' => $e->getMessage()]);
        }
    }
}