<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\IndexDashboardRequest;
use App\Http\Requests\Dashboard\DistribusiWilayahRequest;
use App\Http\Requests\Dashboard\PeringkatOperatorRequest;
use App\Http\Requests\Dashboard\UlasanRequest;
use App\Http\Requests\Dashboard\WaktuRataRequest;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    public function index(
        IndexDashboardRequest $request
    ): JsonResponse {

        $data = $this->service->getDashboard(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data dashboard',
            'data' => $data,
        ]);
    }

    public function distribusiWilayah(
        DistribusiWilayahRequest $request
    ): JsonResponse {

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil distribusi wilayah',
            'data' => $this->service->getDistribusiWilayah(
                $request->validated()
            ),
        ]);
    }

    public function peringkatOperator(
        PeringkatOperatorRequest $request
    ): JsonResponse {

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil peringkat operator',
            'data' => $this->service->getPeringkatOperator(
                $request->validated()
            ),
        ]);
    }

    public function waktuRata(
        WaktuRataRequest $request
    ): JsonResponse {

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil waktu rata-rata',
            'data' => $this->service->getWaktuRata(
                $request->validated()
            ),
        ]);
    }

    public function ulasan(
        UlasanRequest $request
    ): JsonResponse {

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil ulasan',
            'data' => $this->service->getUlasan(
                $request->validated()
            ),
        ]);
    }
}