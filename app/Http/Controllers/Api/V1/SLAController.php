<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SLA\SLAFilterRequest;
use App\Http\Resources\SLA\SLAResource;
use App\Services\SLAService;
use Illuminate\Http\JsonResponse;

class SLAController extends Controller
{
    public function __construct(
        private SLAService $service
    ) {
    }

    /**
     * Menampilkan daftar SLA.
     */
    public function index(
        SLAFilterRequest $request
    ): JsonResponse {

        $data = $this->service->index(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan data SLA',
            'data' => new SLAResource($data),
        ]);
    }

    /**
     * Detail SLA.
     * (Belum diimplementasikan sesuai kebutuhan PM)
     */
    public function show(
        int $sla_id
    ): JsonResponse {

        return response()->json([
            'success' => false,
            'code' => 501,
            'message' => 'Endpoint detail SLA belum diimplementasikan.',
        ], 501);
    }
}