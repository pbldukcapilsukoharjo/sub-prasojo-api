<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DistribusiWilayah\DistribusiWilayahFilterRequest;
use App\Http\Resources\DistribusiWilayah\DistribusiWilayahResource;
use App\Services\DistribusiWilayahService;

class DistribusiWilayahController extends Controller
{
    public function __construct(
        private DistribusiWilayahService $service
    ) {
    }

    /**
     * List distribusi wilayah.
     */
    public function index(
        DistribusiWilayahFilterRequest $request
    ) {

        $data = $this->service->index(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Distribusi wilayah berhasil ditemukan',
            'data' => new DistribusiWilayahResource($data),
        ]);
    }
}