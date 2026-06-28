<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlaRequest;
use App\Http\Resources\SLA\SLAResource;
use App\Exports\SlaLayananExport;
use App\Services\SLAService;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SLAController extends Controller
{
    public function __construct(
        private SLAService $service
    ) {
    }

    /**
     * Menampilkan daftar SLA (beserta KPI global)
     */
    public function index(SlaRequest $request): JsonResponse 
    {
        $data = $this->service->index($request->validated());

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan data SLA',
            // Gunakan Resource jika ada, namun karena response Falah cukup kompleks
            // (termasuk list dan meta), kita langsung me-return hasil atau membungkus di Resource.
            // Sesuai kode Falah sebelumnya, SLAResource digunakan:
            'data' => new SLAResource($data),
        ]);
    }

    /**
     * Menampilkan KPI Global SLA
     */
    public function kpi(SlaRequest $request): JsonResponse 
    {
        $data = $this->service->getKpi($request->validated());

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil',
            'data' => $data,
        ]);
    }

    /**
     * Export Excel data SLA
     */
    public function export(SlaRequest $request): BinaryFileResponse
    {
        $data = $this->service->export($request->validated());
        
        return Excel::download(
            new SlaLayananExport($data), 
            'sla-layanan-' . date('Y-m-d-His') . '.xlsx'
        );
    }
}