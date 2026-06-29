<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\AjuanFilter;
use App\Models\Ajuan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Exports\PengajuanExport;
use Maatwebsite\Excel\Facades\Excel;

final class AjuanService
{
    public function __construct(
        protected AjuanFilter $filter
    ) {}

    public function getAll (array $filters): LengthAwarePaginator {
        $query = Ajuan::query()
            ->with([
                'jenisAjuan',
                'pelapor',
            ]);

        $query = $this->filter->apply(
            $query,
            $filters
        );

        return $query
            ->latest('ajuan_create_datetime')
            ->paginate(10);
    }

    public function getDetail(
        int $ajuanId
    ): Ajuan {
        return Ajuan::query()
            ->with([
                'jenisAjuan',
                'pelapor',
                'logStatuses',
            ])
            ->findOrFail($ajuanId);
    }

    public function getMasterList(array $filters): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Ajuan::query()->with(['pelapor', 'layanan']);
        $query = $this->filter->applyMaster($query, $filters);

        // Limit per page (default 10)
        $perPage = request('per_page', 10);
        $paginator = $query->paginate($perPage);

        // Transform the items to match PRD
        $paginator->getCollection()->transform(function (Ajuan $ajuan) {
            $layananNama = $ajuan->layanan ? $ajuan->layanan->layanan_nama : $ajuan->ajuan_layanan_kode;
            $pelaporName = $ajuan->pelapor ? $ajuan->pelapor->fullname : 'Unknown';
            $pelaporType = $ajuan->isOnline() ? 'Online' : 'Offline';
            $pelaporStr = sprintf('%s (%s)', $pelaporName, $pelaporType);

            return [
                'id' => $ajuan->ajuan_id,
                'no_reg' => $ajuan->ajuan_no_reg,
                'layanan' => $layananNama,
                'kecamatan' => $ajuan->ajuan_kecamatan_name,
                'pelapor' => $pelaporStr,
                'status' => $ajuan->ajuan_status,
                'created_at' => $ajuan->ajuan_create_datetime ? $ajuan->ajuan_create_datetime->format('Y-m-d H:i:s') : null,
            ];
        });

        return $paginator;
    }

    public function exportMaster(array $filters)
    {
        $query = Ajuan::query()->with(['pelapor', 'layanan']);
        $query = $this->filter->applyMaster($query, $filters);

        $filename = 'export_pengajuan_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new PengajuanExport($query), $filename);
    }
}