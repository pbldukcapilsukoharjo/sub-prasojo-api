<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\UlasanService;
use App\Http\Requests\Ulasan\UlasanRequest;
use App\Http\Resources\Ulasan\UlasanResource;

class UlasanController extends Controller
{
    public function __construct(
        private UlasanService $service
    ) {}

    public function index(
        UlasanRequest $request
    ) {

        $data = $this->service->getAll(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan ulasan',
            'data' => new UlasanResource($data)
        ]);
    }
}