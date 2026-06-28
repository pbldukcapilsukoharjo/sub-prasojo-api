<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PeringkatOperatorService;
use App\Http\Requests\PeringkatOperator\PeringkatOperatorFilterRequest;
use App\Http\Resources\PeringkatOperator\PeringkatOperatorResource;
use App\Http\Resources\PeringkatOperator\DetailPeringkatOperatorResource;

class PeringkatOperatorController extends Controller
{
    public function __construct(
        private PeringkatOperatorService $service
    ) {}

    /**
     * List Peringkat Operator
     */
    public function index(
        PeringkatOperatorFilterRequest $request
    ) {
        $data = $this->service->index(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan data operator',
            'data' => new PeringkatOperatorResource($data),
        ]);
    }

    /**
     * Detail Operator
     */
    public function show(
        int $op_id
    ) {
        $data = $this->service->show(
            $op_id
        );

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Detail operator berhasil ditemukan',
            'data' => new DetailPeringkatOperatorResource($data),
        ]);
    }
}