<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ajuan\IndexAjuanRequest;
use App\Http\Requests\Ajuan\ShowAjuanRequest;
use App\Http\Resources\Ajuan\AjuanCollection;
use App\Http\Resources\Ajuan\AjuanDetailResource;
use App\Services\AjuanService;
use Illuminate\Http\JsonResponse;

final class AjuanController extends Controller
{
    public function __construct(
        protected AjuanService $service
    ) {}

    public function index(
        IndexAjuanRequest $request
    ): JsonResponse {
        $data = $this->service->getAll(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data ajuan',
            'data' => new AjuanCollection($data),
        ]);
    }

    public function show(
        ShowAjuanRequest $request,
        int $ajuan_id
    ): JsonResponse {
        $data = $this->service->getDetail($ajuan_id);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil detail ajuan',
            'data' => new AjuanDetailResource($data),
        ]);
    }
}
