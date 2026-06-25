<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Filters\PengajuanFilter;
use App\Http\Responses\ApiResponse;
use App\Services\PengajuanService;
use Illuminate\Http\Request;

final class PengajuanController extends Controller
{
    private PengajuanService $service;

    public function __construct(PengajuanService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filter = new PengajuanFilter($request->all());
        $paginator = $this->service->getList($filter);

        return ApiResponse::paginated('Berhasil mengambil data pengajuan', $paginator);
    }

    public function export(Request $request)
    {
        $filter = new PengajuanFilter($request->all());
        return $this->service->export($filter);
    }
}
