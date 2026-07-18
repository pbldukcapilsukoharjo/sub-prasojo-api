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
use Illuminate\Support\Facades\Log;

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
        try {
            $validated = $request->validated();
            $filter = new WilayahFilter($validated);
            $perPage = (int) ($validated['per_page'] ?? 10);

            $data = $this->wilayahService->getDistribusi($filter, $perPage);

            return ApiResponse::paginated('Berhasil', $data);
        } catch (\Throwable $e) {
            Log::error('[WilayahController@distribusi] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil data distribusi wilayah', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param WilayahRequest $request
     * @return JsonResponse
     */
    public function matriks(WilayahRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $filter = new WilayahFilter($validated);

            $data = $this->wilayahService->getMatriks($filter);

            return ApiResponse::success('Berhasil', $data);
        } catch (\Throwable $e) {
            Log::error('[WilayahController@matriks] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengambil data matriks wilayah', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param WilayahRequest $request
     */
    public function export(WilayahRequest $request)
    {
        try {
            $validated = $request->validated();
            $filter = new WilayahFilter($validated);
            
            $filename = 'export_wilayah_' . Carbon::now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new WilayahDistribusiExport($filter), $filename);
        } catch (\Throwable $e) {
            Log::error('[WilayahController@export] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Gagal mengekspor data wilayah', 500, ['error' => $e->getMessage()]);
        }
    }
}
