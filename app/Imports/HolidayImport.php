<?php

declare(strict_types=1);

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class HolidayImport implements ToCollection
{
    protected array $rows = [];
    protected array $errors = [];

    public function collection(Collection $collection): void
    {
        $isFirstRow = true;

        foreach ($collection as $index => $row) {
            $rowNum = $index + 1;

            $col0 = isset($row[0]) ? trim((string) $row[0]) : '';
            $col1 = isset($row[1]) ? trim((string) $row[1]) : '';

            // Handle header row in Excel
            if ($isFirstRow) {
                $isFirstRow = false;
                if ($col0 === '' || str_contains(strtolower($col0), 'tanggal')) {
                    continue;
                }
            }

            // Skip completely empty rows
            if ($col0 === '' && $col1 === '') {
                continue;
            }

            $tanggal = $this->parseDate($row[0] ?? null);
            $keterangan = $col1;

            if (!$tanggal) {
                $this->errors[] = "Baris {$rowNum}: Format tanggal '{$col0}' tidak valid. Gunakan format YYYY-MM-DD.";
                continue;
            }

            if ($keterangan === '') {
                $this->errors[] = "Baris {$rowNum}: Keterangan tidak boleh kosong.";
                continue;
            }

            $this->rows[] = [
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'row_number' => $rowNum,
            ];
        }
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                $dateObj = ExcelDate::excelToDateTimeObject((float) $value);
                return $dateObj->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $strValue = trim((string) $value);
        if ($strValue === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $strValue)) {
            [$year, $month, $day] = explode('-', $strValue);
            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return $strValue;
            }
        }

        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $strValue)) {
            [$year, $month, $day] = explode('/', $strValue);
            if (checkdate((int) $month, (int) $day, (int) $year)) {
                return sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);
            }
        }

        return null;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
