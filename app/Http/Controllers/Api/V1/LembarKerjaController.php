<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LembarKerja\IndexLembarKerjaRequest;
use App\Http\Requests\LembarKerja\ShowLembarKerjaRequest;
use App\Http\Resources\LembarKerja\LembarKerjaCollection;
use App\Http\Resources\LembarKerja\LembarKerjaResource;
use App\Services\LembarKerjaService;
use Illuminate\Http\JsonResponse;

final class LembarKerjaController extends Controller
{
    public function __construct(
        protected LembarKerjaService $service
    ) {}

    /**
     * GET /api/v1/lembar-kerja
     */
    public function index(
        IndexLembarKerjaRequest $request
    ): JsonResponse {
        $data = $this->service->getAll(
            $request->validated()
        );

        $resource = new LembarKerjaCollection($data);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan lembar kerja',
            'data' => $resource->collection,
            'meta' => [
                'page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'total_page' => $data->lastPage(),
            ]
        ]);
    }

    /**
     * GET /api/v1/lembar-kerja/{lk_id}
     */
    public function show(
        ShowLembarKerjaRequest $request,
        int $lk_id
    ): JsonResponse {
        $data = $this->service->getDetail($lk_id);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil detail lembar kerja',
            'data' => new LembarKerjaResource($data),
        ]);
    }
}