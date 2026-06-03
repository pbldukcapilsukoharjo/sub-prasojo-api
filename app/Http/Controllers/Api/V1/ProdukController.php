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

final class ProdukController extends Controller
{
    public function __construct(
        protected ProdukService $service
    ) {}

    public function index(
        IndexProdukRequest $request
    ): JsonResponse {
        $data = $this->service->getAll(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data produk',
            'data' => new ProdukCollection($data),
        ]);
    }

    public function show(
        ShowProdukRequest $request,
        int $produk_id
    ): JsonResponse {
        $data = $this->service->getDetail($produk_id);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil detail produk',
            'data' => new ProdukDetailResource($data),
        ]);
    }
}