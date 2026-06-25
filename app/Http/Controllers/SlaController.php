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

class SlaController extends Controller
{
    protected SlaService $slaService;

    public function __construct(SlaService $slaService)
    {
        $this->slaService = $slaService;
    }

    public function kpi(Request $request): JsonResponse
    {
        $filter = new SlaFilter($request->all());
        $data = $this->slaService->getKpi($filter);

        return ApiResponse::success('Berhasil mengambil KPI SLA', $data);
    }

    public function layanan(Request $request): JsonResponse
    {
        $filter = new SlaFilter($request->all());
        $data = $this->slaService->getLayanan($filter);

        return ApiResponse::paginated('Berhasil mengambil data komparasi layanan', $data);
    }

    public function export(Request $request)
    {
        $filter = new SlaFilter($request->all());
        $data = $this->slaService->exportLayanan($filter);

        $filename = 'export_sla_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new SlaLayananExport($data), $filename);
    }
}
