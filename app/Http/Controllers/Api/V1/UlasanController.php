<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ulasan\UlasanFilterRequest;
use App\Http\Resources\Ulasan\UlasanKpiResource;
use App\Http\Resources\Ulasan\UlasanResource;
use App\Services\UlasanService;
use Illuminate\Http\JsonResponse;

final class UlasanController extends Controller
{
    public function __construct(
        private readonly UlasanService $service
    ) {
    }

    /**
     * Menampilkan daftar ulasan.
     */
    public function index(
        UlasanFilterRequest $request
    ): JsonResponse {

        $result = $this->service->index(
            $request->validated()
        );

        return response()->json([
            'status'  => true,
            'code'    => 200,
            'message' => 'Berhasil mendapatkan ulasan',
            'data'    => new UlasanResource($result),
        ]);
    }

    /**
     * Menampilkan KPI ulasan.
     */
    public function kpi(
        UlasanFilterRequest $request
    ): JsonResponse {

        $result = $this->service->kpi(
            $request->validated()
        );

        return response()->json([
            'status'  => true,
            'code'    => 200,
            'message' => 'Berhasil',
            'data'    => new UlasanKpiResource($result),
        ]);
    }
}