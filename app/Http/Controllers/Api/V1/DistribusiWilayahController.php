<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DistribusiWilayahService;
use App\Http\Requests\DistribusiWilayah\DistribusiWilayahRequest;
use App\Http\Resources\DistribusiWilayah\DistribusiWilayahItemResource;

class DistribusiWilayahController extends Controller
{
    public function __construct(
        private DistribusiWilayahService $service
    ) {}

    public function index(
        DistribusiWilayahRequest $request
    ) {

        $data = $this->service->getAll(
            $request->validated()
        );

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Distribusi wilayah berhasil ditemukan',
            'data' => DistribusiWilayahItemResource::collection($data['list'])->resolve(),
            'meta' => $data['meta']
        ]);
    }
}