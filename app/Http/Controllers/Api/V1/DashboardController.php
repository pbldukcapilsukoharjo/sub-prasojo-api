<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    /**
     * GET /api/v1/dashboard
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data dashboard',
            'data' => $this->service->getSummary(),
        ]);
    }

    /**
     * GET /api/v1/dashboard/distribusi-wilayah
     */
    public function distribusiWilayah(
        Request $request
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil distribusi wilayah',
            'data' => $this->service->getDistribusiWilayah(
                $request->all()
            ),
        ]);
    }

    /**
     * GET /api/v1/dashboard/peringkat-operator
     */
    public function peringkatOperator(
        Request $request
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil peringkat operator',
            'data' => $this->service->getPeringkatOperator(
                $request->all()
            ),
        ]);
    }
}