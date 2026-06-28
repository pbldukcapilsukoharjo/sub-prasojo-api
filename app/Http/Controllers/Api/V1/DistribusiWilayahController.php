<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DistribusiWilayah\DistribusiWilayahFilterRequest;
use App\Http\Resources\DistribusiWilayah\DistribusiWilayahResource;
use App\Services\DistribusiWilayahService;
use Illuminate\Http\JsonResponse;

final class DistribusiWilayahController extends Controller
{
    public function __construct(
        private readonly DistribusiWilayahService $service
    ) {
    }

    /**
     * GET /api/v1/distribusi-wilayah
     *
     * Menampilkan distribusi ajuan berdasarkan wilayah.
     */
    public function index(
        DistribusiWilayahFilterRequest $request
    ): JsonResponse {

        $result = $this->service->index(
            $request->validated()
        );

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Distribusi wilayah berhasil ditemukan.',
            'data' => new DistribusiWilayahResource($result),
        ]);
    }
}