<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\SlaLayananExport;
use App\Filters\SlaFilter;
use App\Http\Responses\ApiResponse;
use App\Services\SlaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

final class SlaController extends Controller
{
    protected SlaService $slaService;

    public function __construct(SlaService $slaService)
    {
        $this->slaService = $slaService;
    }

    public function kpi(Request $request): JsonResponse
    {
        try {
            $filter = new SlaFilter($request->all());
            $data = $this->slaService->getKpi($filter);

            return ApiResponse::success('Berhasil mengambil KPI SLA', $data);
        } catch (\Throwable $e) {
            Log::error('[SlaController@kpi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil KPI SLA', 500, ['error' => $e->getMessage()]);
        }
    }

    public function layanan(Request $request): JsonResponse
    {
        try {
            $filter = new SlaFilter($request->all());
            $data = $this->slaService->getLayanan($filter);

            return ApiResponse::paginated('Berhasil mengambil data komparasi layanan', $data);
        } catch (\Throwable $e) {
            Log::error('[SlaController@layanan] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil data komparasi layanan', 500, ['error' => $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        try {
            $filter = new SlaFilter($request->all());
            $data = $this->slaService->exportLayanan($filter);

            $filename = 'export_sla_' . Carbon::now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new SlaLayananExport($data), $filename);
        } catch (\Throwable $e) {
            Log::error('[SlaController@export] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengekspor data SLA', 500, ['error' => $e->getMessage()]);
        }
    }
}
