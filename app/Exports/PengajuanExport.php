<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Ajuan;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class PengajuanExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    private Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'No. Registrasi',
            'Layanan',
            'Kecamatan',
            'Pelapor',
            'Status',
            'Tanggal',
        ];
    }

    /**
     * @param Ajuan $ajuan
     */
    public function map($ajuan): array
    {
        $layananNama = $ajuan->layanan ? $ajuan->layanan->layanan_nama : $ajuan->ajuan_layanan_kode;
        $pelaporName = $ajuan->pelapor ? $ajuan->pelapor->fullname : 'Unknown';

        // Construct a descriptive pelapor string based on PRD
        $pelaporType = $ajuan->isOnline() ? 'Online' : 'Offline';
        $pelaporTipe = $ajuan->isMandiri() ? 'Mandiri' : 'Operator';
        $pelaporStr = sprintf('%s (%s - %s)', $pelaporName, $pelaporType, $pelaporTipe);

        return [
            $ajuan->ajuan_no_reg,
            $layananNama,
            $ajuan->ajuan_kecamatan_name,
            $pelaporStr,
            $ajuan->ajuan_status,
            $ajuan->ajuan_create_datetime ? $ajuan->ajuan_create_datetime->format('d-m-Y H:i:s') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
