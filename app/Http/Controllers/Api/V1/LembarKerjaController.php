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

    public function index(
        IndexLembarKerjaRequest $request
    ): JsonResponse {

        $data = $this->service->getAll(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan lembar kerja',
            'data' => LembarKerjaListResource::collection(
                collect($data->items())
            ),
            'meta' => [
                'page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'total_page' => $data->lastPage(),
            ],
        ]);
    }

    public function show(
        ShowLembarKerjaRequest $request,
        string $lk_id
    ): JsonResponse {

        $data = $this->service->getDetail(
            (int) $lk_id
        );

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil detail lembar kerja',
            'data' => new LembarKerjaDetailResource(
                $data
            ),
        ]);
    }
}