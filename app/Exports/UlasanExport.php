<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UlasanExport implements FromCollection, WithHeadings, WithStyles
{
    protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        // Data format mapping matches UlasanService::getForExport mapping
        return $this->data->map(function ($item) {
            return [
                'id_review' => $item['id_review'],
                'tanggal' => $item['tanggal'],
                'no_reg' => $item['no_reg'],
                'layanan' => $item['layanan'],
                'rating' => $item['rating'],
                'komentar' => $item['komentar'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID Review',
            'Tanggal',
            'No. Registrasi',
            'Layanan',
            'Rating',
            'Komentar',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
