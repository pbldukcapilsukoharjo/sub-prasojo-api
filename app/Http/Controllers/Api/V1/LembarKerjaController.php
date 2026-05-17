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

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data lembar kerja',
            'data' => new LembarKerjaCollection($data),
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
            'success' => true,
            'message' => 'Berhasil mengambil detail lembar kerja',
            'data' => new LembarKerjaResource($data),
        ]);
    }
}