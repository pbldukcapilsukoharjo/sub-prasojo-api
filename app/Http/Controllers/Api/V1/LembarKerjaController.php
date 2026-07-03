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
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;

final class LembarKerjaController extends Controller
{
    public function __construct(
        protected LembarKerjaService $service
    ) {}

    public function index(
        IndexLembarKerjaRequest $request
    ): JsonResponse {
        try {
            $data = $this->service->getAll(
                $request->validated()
            );

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'Berhasil mendapatkan lembar kerja',
                'data' => LembarKerjaListResource::collection(
                    collect($data->items())
                )->resolve(),
                'meta' => [
                    'page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'total_page' => $data->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[LembarKerjaController@index] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mendapatkan lembar kerja', 500, ['error' => $e->getMessage()]);
        }
    }

    public function show(
        ShowLembarKerjaRequest $request,
        string $lk_id
    ): JsonResponse {
        try {
            $data = $this->service->getDetail(
                (int) $lk_id
            );

            return ApiResponse::success(
                'Berhasil mengambil detail lembar kerja',
                new LembarKerjaDetailResource($data)
            );
        } catch (\Throwable $e) {
            Log::error('[LembarKerjaController@show] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil detail lembar kerja', 500, ['error' => $e->getMessage()]);
        }
    }
}