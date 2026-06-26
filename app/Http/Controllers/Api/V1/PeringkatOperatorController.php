<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeringkatOperator\PeringkatOperatorRequest;
use App\Http\Requests\PeringkatOperator\DetailPeringkatOperatorRequest;
use App\Http\Resources\PeringkatOperator\PeringkatOperatorResource;
use App\Http\Resources\PeringkatOperator\DetailPeringkatOperatorResource;
use App\Services\PeringkatOperatorService;

class PeringkatOperatorController extends Controller
{
    public function __construct(
        private PeringkatOperatorService $service
    ) {}

    /**
     * GET /api/v1/peringkat-operator
     */
    public function index(PeringkatOperatorRequest $request)
    {
        $result = $this->service->getAll(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan data operator',
            'data' => new PeringkatOperatorResource($result)
        ]);
    }

    /**
     * GET /api/v1/peringkat-operator/{op_id}
     */
    public function show(
        DetailPeringkatOperatorRequest $request,
        int $op_id
    ) {

        $result = $this->service->detail($op_id);

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Detail operator berhasil ditemukan',
            'data' => new DetailPeringkatOperatorResource($result)
        ]);
    }
}