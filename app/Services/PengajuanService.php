<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\PengajuanExport;
use App\Filters\PengajuanFilter;
use App\Models\Ajuan;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class PengajuanService
{
    public function getList(PengajuanFilter $filter): LengthAwarePaginator
    {
        $query = Ajuan::query()->with(['pelapor', 'layanan']);
        $query = $filter->apply($query);

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

    public function export(PengajuanFilter $filter)
    {
        $query = Ajuan::query()->with(['pelapor', 'layanan']);
        $query = $filter->apply($query);

        $filename = 'export_pengajuan_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new PengajuanExport($query), $filename);
    }
}
