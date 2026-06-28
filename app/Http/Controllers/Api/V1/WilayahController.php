<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WilayahRequest;
use App\Filters\WilayahFilter;
use App\Http\Responses\ApiResponse;
use App\Services\WilayahService;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Exports\WilayahDistribusiExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class WilayahController extends Controller
{
    public function __construct(
        private WilayahService $wilayahService
    ) {
    }

    /**
     * @param WilayahRequest $request
     * @return JsonResponse
     */
    public function distribusi(WilayahRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $filter = new WilayahFilter($validated);
        $perPage = (int) ($validated['per_page'] ?? 10);

        $data = $this->wilayahService->getDistribusi($filter, $perPage);

        return ApiResponse::paginated('Berhasil', $data);
    }

    /**
     * @param WilayahRequest $request
     * @return BinaryFileResponse
     */
    public function export(WilayahRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();
        $filter = new WilayahFilter($validated);
        
        $filename = 'export_wilayah_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new WilayahDistribusiExport($filter), $filename);
    }
}
