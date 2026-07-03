<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produk\IndexProdukRequest;
use App\Http\Requests\Produk\ShowProdukRequest;
use App\Http\Resources\Produk\ProdukCollection;
use App\Http\Resources\Produk\ProdukDetailResource;
use App\Services\ProdukService;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Log;

final class ProdukController extends Controller
{
    public function __construct(
        protected ProdukService $service
    ) {}

    public function index(
        IndexProdukRequest $request
    ): JsonResponse {
        try {
            $data = $this->service->getAll(
                $request->validated()
            );

            return response()->json(array_merge([
                'status' => true,
                'code' => 200,
                'message' => 'Berhasil mengambil data produk',
            ], (new ProdukCollection($data))->resolve()));
        } catch (\Throwable $e) {
            Log::error('[ProdukController@index] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil data produk', 500, ['error' => $e->getMessage()]);
        }
    }

    public function show(
        ShowProdukRequest $request,
        int $produk_id
    ): JsonResponse {
        try {
            $data = $this->service->getDetail($produk_id);

            return ApiResponse::success('Berhasil mengambil detail produk', new ProdukDetailResource($data));
        } catch (\Throwable $e) {
            Log::error('[ProdukController@show] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil detail produk', 500, ['error' => $e->getMessage()]);
        }
    }
}