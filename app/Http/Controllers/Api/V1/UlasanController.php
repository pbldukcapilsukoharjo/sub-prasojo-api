<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exports\UlasanExport;
use App\Filters\UlasanFilter;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\UlasanService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UlasanController extends Controller
{
    protected UlasanService $ulasanService;

    public function __construct(UlasanService $ulasanService)
    {
        $this->ulasanService = $ulasanService;
    }

    public function kpi(Request $request)
    {
        $filter = new UlasanFilter($request->all());
        $data = $this->ulasanService->getKpi($filter);

        return ApiResponse::success('Berhasil', $data);
    }

    public function index(Request $request)
    {
        $filter = new UlasanFilter($request->all());
        $data = $this->ulasanService->getList($filter);

        return ApiResponse::paginated('Berhasil', $data);
    }

    public function export(Request $request)
    {
        $filter = new UlasanFilter($request->all());
        $data = $this->ulasanService->getForExport($filter);

        $filename = 'export_ulasan_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new UlasanExport($data), $filename);
    }
}