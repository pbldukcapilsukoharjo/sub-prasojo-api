<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Filters\DashboardFilter;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function kpi(Request $request): JsonResponse
    {
        $filter = new DashboardFilter($request->all());
        $data = $this->dashboardService->getKpi($filter);

        return ApiResponse::success('Berhasil mengambil KPI Dashboard', $data);
    }

    public function chartTrend(Request $request): JsonResponse
    {
        $filter = new DashboardFilter($request->all());
        $data = $this->dashboardService->getChartTrend($filter);

        return ApiResponse::success('Berhasil mengambil Chart Trend', $data);
    }

    public function topWilayah(Request $request): JsonResponse
    {
        $filter = new DashboardFilter($request->all());
        $data = $this->dashboardService->getTopWilayah($filter);

        return ApiResponse::success('Berhasil mengambil Top Wilayah', $data);
    }
}