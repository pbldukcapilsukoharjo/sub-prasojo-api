<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ajuan\IndexAjuanRequest;
use App\Http\Requests\Ajuan\ShowAjuanRequest;
use App\Http\Resources\Ajuan\AjuanCollection;
use App\Http\Resources\Ajuan\AjuanDetailResource;
use App\Services\AjuanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;

final class AjuanController extends Controller
{
    public function __construct(
        protected AjuanService $service
    ) {}

    public function index (IndexAjuanRequest $request): JsonResponse {
        $data = $this->service->getAll(
            $request->validated()
        );

        return response()->json(array_merge([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mengambil data ajuan',
        ], (new AjuanCollection($data))->resolve()));
    }

    public function show(
        ShowAjuanRequest $request,
        int $ajuan_id
    ): JsonResponse {
        $data = $this->service->getDetail($ajuan_id);

        return ApiResponse::success('Berhasil mengambil detail ajuan', new AjuanDetailResource($data));
    }

    public function masterIndex(Request $request): JsonResponse
    {
        $paginator = $this->service->getMasterList($request->all());
        return ApiResponse::paginated('Berhasil mengambil data pengajuan', $paginator);
    }

    public function masterExport(Request $request)
    {
        return $this->service->exportMaster($request->all());
    }
}
