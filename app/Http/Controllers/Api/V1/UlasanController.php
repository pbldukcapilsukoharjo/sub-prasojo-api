<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ulasan\UlasanFilterRequest;
use App\Http\Resources\Ulasan\UlasanResource;
use App\Services\UlasanService;
use Illuminate\Http\JsonResponse;

class UlasanController extends Controller
{
    public function __construct(
        private readonly UlasanService $service
    ) {
    }

    /**
     * Menampilkan daftar ulasan.
     */
    public function index(
        UlasanFilterRequest $request
    ): JsonResponse {

        $result = $this->service->index(
            $request->validated()
        );

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan data ulasan.',
            'data' => new UlasanResource($result),
        ]);
    }
}