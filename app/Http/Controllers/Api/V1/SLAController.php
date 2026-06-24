<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SLA\SLAResource;
use App\Http\Requests\SLARequest;
use App\Services\SLAService;
use App\Filters\SLAFilter;

class SLAController extends Controller
{
    public function __construct(
        protected SLAService $service,
        protected SLAFilter $filter
    ) {
    }

    public function index(SLARequest $request)
    {
        $filters = $this->filter->transform(
            $request->validated()
        );

        $data = $this->service->getAll($filters);

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan data SLA',
            'data' => new SLAResource($data)
        ]);
    }
}