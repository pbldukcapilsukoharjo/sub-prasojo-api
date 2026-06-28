<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exports\UlasanExport;
use App\Filters\UlasanFilter;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\UlasanService;
use App\Http\Requests\Ulasan\UlasanRequest;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

final class UlasanController extends Controller
{
    public function __construct(
        private readonly UlasanService $ulasanService
    ) {}

    public function kpi(UlasanRequest $request): JsonResponse
    {
        $filter = new UlasanFilter($request->validated());
        $data = $this->ulasanService->getKpi($filter);

        return ApiResponse::success('Berhasil', $data);
    }

    public function index(UlasanRequest $request): JsonResponse
    {
        $filter = new UlasanFilter($request->validated());
        $data = $this->ulasanService->getList($filter);

        return ApiResponse::paginated('Berhasil', $data);
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $data = $this->ulasanService->getForExport();

        $filename = 'export_ulasan_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new UlasanExport($data), $filename);
    }
}