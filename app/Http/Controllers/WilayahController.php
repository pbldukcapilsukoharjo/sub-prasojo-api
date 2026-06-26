<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Filters\WilayahFilter;
use App\Http\Responses\ApiResponse;
use App\Services\WilayahService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Exports\WilayahDistribusiExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class WilayahController extends Controller
{
    protected WilayahService $wilayahService;

    public function __construct(WilayahService $wilayahService)
    {
        $this->wilayahService = $wilayahService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function distribusi(Request $request): JsonResponse
    {
        $filter = new WilayahFilter($request->all());
        $perPage = (int) $request->input('per_page', 10);

        $data = $this->wilayahService->getDistribusi($filter, $perPage);

        return ApiResponse::paginated('Berhasil', $data);
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filter = new WilayahFilter($request->all());
        
        $filename = 'export_wilayah_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new WilayahDistribusiExport($filter), $filename);
    }
}
