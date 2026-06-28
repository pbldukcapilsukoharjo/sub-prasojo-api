<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sla\SlaKpiRequest;
use App\Http\Requests\Sla\SlaListRequest;
use App\Http\Resources\Sla\SlaIndexResource;
use App\Http\Resources\Sla\SlaKpiResource;
use App\Services\SlaService;
use Illuminate\Http\JsonResponse;

final class SlaController extends Controller
{
    public function __construct(
        private readonly SlaService $service,
    ) {}

    public function index(
        SlaListRequest $request,
    ): JsonResponse {

        $result = $this->service->getList(
            $request->validated()
        );

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan data SLA',
            'data' => (new SlaIndexResource(
                $result
            ))->resolve(),
        ]);
    }

    public function kpi(
        SlaKpiRequest $request,
    ): JsonResponse {

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil',
            'data' => (new SlaKpiResource(
                $this->service->getKpi(
                    $request->validated()
                )
            ))->resolve(),
        ]);
    }
}