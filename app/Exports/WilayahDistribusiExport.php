<?php

declare(strict_types=1);

namespace App\Exports;

use App\Filters\WilayahFilter;
use App\Services\WilayahService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class WilayahDistribusiExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithEvents, ShouldAutoSize
{
    protected WilayahFilter $filter;
    protected WilayahService $service;
    protected array $exportPayload;
    protected array $rowTypes = [];

    public function __construct(WilayahFilter $filter)
    {
        $this->filter = $filter;
        $this->service = app(WilayahService::class);
        $this->exportPayload = $this->service->getDistribusiExportData($this->filter);
    }

    public function title(): string
    {
        return 'Distribusi Wilayah';
    }

    public function array(): array
    {
        $rows = [];
        $layanans = $this->exportPayload['layanans'];
        $data = $this->exportPayload['data'];

        $no = 1;
        $globalTotalAjuan = 0;
        $globalTotalSelesai = 0;
        $globalLayananTotals = array_fill_keys(array_keys($layanans), 0);

        $currentRow = 6;

        foreach ($data as $kec) {
            // Parent Row: Kecamatan
            $kecRow = [
                $no++,
                $kec['kode_wilayah'],
                'Kecamatan ' . $kec['nama_wilayah'],
                $kec['total_ajuan'],
                $kec['total_selesai'],
                $kec['rasio_selesai_persen'] / 100,
                $kec['rata_rata_waktu'],
            ];

            foreach (array_keys($layanans) as $lKode) {
                $count = $kec['layanan_counts'][$lKode] ?? 0;
                $kecRow[] = $count;
                $globalLayananTotals[$lKode] += $count;
            }

            $rows[] = $kecRow;
            $this->rowTypes[$currentRow] = [
                'type' => 'kecamatan',
                'level' => 0,
            ];
            $currentRow++;

            $globalTotalAjuan += $kec['total_ajuan'];
            $globalTotalSelesai += $kec['total_selesai'];

            // Child Rows: Desa / Kelurahan
            if (!empty($kec['desas'])) {
                foreach ($kec['desas'] as $desa) {
                    $desaRow = [
                        '',
                        $desa['kode_wilayah'],
                        '  Desa/Kel. ' . $desa['nama_wilayah'],
                        $desa['total_ajuan'],
                        $desa['total_selesai'],
                        $desa['rasio_selesai_persen'] / 100,
                        $desa['rata_rata_waktu'],
                    ];

                    foreach (array_keys($layanans) as $lKode) {
                        $desaRow[] = $desa['layanan_counts'][$lKode] ?? 0;
                    }

                    $rows[] = $desaRow;
                    $this->rowTypes[$currentRow] = [
                        'type' => 'desa',
                        'level' => 1,
                    ];
                    $currentRow++;
                }
            }
        }

        // Summary Bottom Row
        $globalRasio = $globalTotalAjuan > 0 ? ($globalTotalSelesai / $globalTotalAjuan) : 0;
        $totalRow = [
            '',
            'TOTAL',
            'TOTAL GLOBAL',
            $globalTotalAjuan,
            $globalTotalSelesai,
            $globalRasio,
            '-',
        ];
        foreach (array_keys($layanans) as $lKode) {
            $totalRow[] = $globalLayananTotals[$lKode] ?? 0;
        }

        $rows[] = $totalRow;
        $this->rowTypes[$currentRow] = [
            'type' => 'total',
            'level' => 0,
        ];

        return $rows;
    }

    public function headings(): array
    {
        $layanans = $this->exportPayload['layanans'];
        $now = Carbon::now()->format('d-m-Y H:i');

        $groupHeader = [
            'INFORMASI WILAYAH', '', '',
            'METRIK UTAMA & SLA', '', '', '',
        ];
        if (!empty($layanans)) {
            $groupHeader[] = 'RINCIAN PER JENIS LAYANAN (BREAKDOWN)';
            for ($i = 1; $i < count($layanans); $i++) {
                $groupHeader[] = '';
            }
        }

        $detailHeader = [
            'No',
            'Kode Wilayah',
            'Nama Wilayah',
            'Total Ajuan',
            'Total Selesai',
            'Rasio Selesai (%)',
            'Rata-rata SLA',
        ];

        foreach (array_keys($layanans) as $kode) {
            $detailHeader[] = (string) $kode;
        }

        return [
            ['LAPORAN REKAPITULASI DISTRIBUSI PERMOHONAN PER WILAYAH'],
            ['Tanggal Cetak: ' . $now],
            [],
            $groupHeader,
            $detailHeader,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $layanans = $this->exportPayload['layanans'];
                $totalCols = 7 + count($layanans);
                $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

                // Merge Title Row (Row 1)
                $sheet->mergeCells("A1:{$lastColumnLetter}1");
                $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Merge Subtitle Row (Row 2)
                $sheet->mergeCells("A2:{$lastColumnLetter}2");
                $sheet->getStyle("A2")->getFont()->setItalic(true)->setSize(10);
                $sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Merge Group Headers (Row 4)
                $sheet->mergeCells("A4:C4");
                $sheet->mergeCells("D4:G4");
                if (count($layanans) > 0) {
                    $startLayananCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(8);
                    $sheet->mergeCells("{$startLayananCol}4:{$lastColumnLetter}4");
                }

                // Styling Headers (Row 4 & 5)
                $headerRange = "A4:{$lastColumnLetter}5";
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D9E1F2');

                // Styling Data Rows & Outline Levels
                foreach ($this->rowTypes as $rowIndex => $info) {
                    if ($info['type'] === 'kecamatan') {
                        $range = "A{$rowIndex}:{$lastColumnLetter}{$rowIndex}";
                        $sheet->getStyle($range)->getFont()->setBold(true);
                        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E2EFDA');
                        $sheet->getRowDimension($rowIndex)->setOutlineLevel(0);
                    } elseif ($info['type'] === 'desa') {
                        $range = "A{$rowIndex}:{$lastColumnLetter}{$rowIndex}";
                        $sheet->getRowDimension($rowIndex)->setOutlineLevel(1);
                        $sheet->getRowDimension($rowIndex)->setVisible(true);
                        $sheet->getRowDimension($rowIndex)->setCollapsed(false);
                    } elseif ($info['type'] === 'total') {
                        $range = "A{$rowIndex}:{$lastColumnLetter}{$rowIndex}";
                        $sheet->getStyle($range)->getFont()->setBold(true);
                        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FCE4D6');
                    }

                    // Format Percentages
                    $sheet->getStyle("F{$rowIndex}")->getNumberFormat()->setFormatCode('0.00%');
                }

                $sheet->setShowSummaryBelow(true);

                // Add Borders to table
                $lastRow = 5 + count($this->rowTypes);
                $tableRange = "A4:{$lastColumnLetter}{$lastRow}";
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}
