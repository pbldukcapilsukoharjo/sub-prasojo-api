<?php

declare(strict_types=1);

namespace App\Exports;

use App\Filters\OperatorFilter;
use App\Services\OperatorService;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OperatorRankingExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected OperatorFilter $filter;
    protected OperatorService $service;
    protected int $rowNumber = 0;

    public function __construct(OperatorFilter $filter)
    {
        $this->filter = $filter;
        $this->service = app(OperatorService::class);
    }

    public function query()
    {
        return $this->service->buildRankingQuery($this->filter);
    }

    public function headings(): array
    {
        return [
            'Peringkat',
            'ID Operator',
            'Nama Operator',
            'Total Berkas',
            'Rata-rata Waktu (Menit)',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->id_operator,
            $row->nama,
            (int) $row->total_berkas,
            round((float) $row->rata_rata_waktu_menit, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
