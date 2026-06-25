<?php

declare(strict_types=1);

namespace App\Exports;

use App\Filters\WilayahFilter;
use App\Services\WilayahService;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WilayahDistribusiExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected WilayahFilter $filter;
    protected WilayahService $service;

    public function __construct(WilayahFilter $filter)
    {
        $this->filter = $filter;
        $this->service = app(WilayahService::class);
    }

    public function query()
    {
        return $this->service->buildDistribusiQuery($this->filter);
    }

    public function headings(): array
    {
        return [
            'ID Kecamatan',
            'Nama Kecamatan',
            'Total Ajuan',
            'Rata-rata Waktu',
            'Rasio Selesai (%)',
        ];
    }

    public function map($row): array
    {
        $formatted = $this->service->formatDistribusiItem($row);

        return [
            $formatted['id_kecamatan'],
            $formatted['nama_kecamatan'],
            $formatted['total_ajuan'],
            $formatted['rata_rata_waktu'],
            $formatted['rasio_selesai_persen'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
