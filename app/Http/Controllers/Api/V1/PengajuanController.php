<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PengajuanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Ajuan\IndexAjuanRequest;
use App\Http\Requests\LembarKerja\IndexLembarKerjaRequest;
use App\Http\Requests\Produk\IndexProdukRequest;
use App\Http\Responses\ApiResponse;

final class PengajuanController extends Controller
{
    public function __construct(
        protected PengajuanService $service
    ) {}

    public function getLembarKerja(IndexLembarKerjaRequest $request): JsonResponse
    {
        $result = $this->service->getLembarKerjaList($request->validated());
        
        return ApiResponse::paginated(
            'Berhasil mengambil data lembar kerja', 
            $result['paginator'],
            [
                'chart_status' => $result['chart_status'],
                'chart_layanan' => $result['chart_layanan']
            ]
        );
    }

    public function getAjuan(IndexAjuanRequest $request): JsonResponse
    {
        $data = $this->service->getAjuanList($request->validated());
        return ApiResponse::paginated('Berhasil mengambil data ajuan', $data);
    }

    public function getProduk(IndexProdukRequest $request): JsonResponse
    {
        $data = $this->service->getProdukList($request->validated());
        return ApiResponse::paginated('Berhasil mengambil data produk', $data);
    }

    public function getDetailTimeline(Request $request, int $ajuan_id): JsonResponse
    {
        $data = $this->service->getDetailTimeline($ajuan_id);
        return ApiResponse::success('Berhasil mengambil detail timeline pengajuan', $data);
    }
}
