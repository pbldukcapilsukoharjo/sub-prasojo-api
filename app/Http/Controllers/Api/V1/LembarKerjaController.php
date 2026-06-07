<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LembarKerja\IndexLembarKerjaRequest;
use App\Http\Requests\LembarKerja\ShowLembarKerjaRequest;
use App\Http\Resources\LembarKerja\LembarKerjaDetailResource;
use App\Http\Resources\LembarKerja\LembarKerjaListResource;
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

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan lembar kerja',
            'data' => LembarKerjaListResource::collection(
                $data->items()
            ),
            'meta' => [
                'page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'total_page' => $data->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/lembar-kerja/{lk_id}
     */
    public function show(
        ShowLembarKerjaRequest $request,
        int $lk_id  // Changed from int to string
    ): JsonResponse {
        $data = $this->service->getDetail(
             $lk_id  // Cast to int here
        );

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil detail lembar kerja',
            'data' => new LembarKerjaDetailResource(
                $data
            ),
        ]);
    }
}