<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PeringkatOperatorService;
use App\Http\Requests\PeringkatOperator\PeringkatOperatorFilterRequest;
use App\Http\Resources\PeringkatOperator\PeringkatOperatorResource;
use App\Http\Resources\PeringkatOperator\DetailPeringkatOperatorResource;
use Illuminate\Http\JsonResponse;

// Polesan Amru: penambahan strict_types, final class modifier
final class PeringkatOperatorController extends Controller
{
    public function __construct(
        private PeringkatOperatorService $service
    ) {}

    /**
     * List Peringkat Operator
     */
    public function index(PeringkatOperatorFilterRequest $request): JsonResponse
    {
        // KODE MILIK FALAH
        $data = $this->service->index(
            $request->validated()
        );

        // Polesan Amru: standarisasi API Response, mengubah key 'success' menjadi 'status' => 'success'
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Berhasil mendapatkan data operator',
            'data' => new PeringkatOperatorResource($data),
        ]);
    }

    /**
     * Detail Operator
     */
    public function show(int $op_id): JsonResponse
    {
        // KODE MILIK FALAH
        $data = $this->service->show(
            $op_id
        );

        // Polesan Amru: standarisasi API Response, mengubah key 'success' menjadi 'status' => 'success'
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Detail operator berhasil ditemukan',
            'data' => new DetailPeringkatOperatorResource($data),
        ]);
    }
}