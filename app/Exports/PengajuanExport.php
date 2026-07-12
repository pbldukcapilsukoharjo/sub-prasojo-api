<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Prasojo\Ajuan;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class PengajuanExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    private Builder $query;
    private string $kategori;
    private int $rowNumber = 0;

    public function __construct(Builder $query, string $kategori = 'all')
    {
        $this->query = $query;
        $this->kategori = $kategori;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        if ($this->kategori === 'lembar_kerja') {
            return [
                'No',
                'NO. REG',
                'KODE AJUAN',
                'KODE PRODUK',
                'JALUR ONLINE / OFFLINE',
                'PELAPOR',
                'STATUS',
                'TANGGAL',
                'KECAMATAN',
            ];
        }

        if ($this->kategori === 'produk') {
            return [
                'No',
                'NO. REG',
                'KODE AJUAN',
                'NOMOR (KK, KTP-EL, KIA, DLL)',
                'NAMA IDENTITAS PRODUK',
                'STATUS',
                'TANGGAL',
                'KECAMATAN',
            ];
        }

        return [
            'No',
            'NO. REG',
            'KODE LAYANAN',
            'JENIS AJUAN',
            'JALUR ONLINE / OFFLINE',
            'PELAPOR',
            'STATUS',
            'TANGGAL',
            'KECAMATAN',
        ];
    }

    /**
     * @param Ajuan $ajuan
     */
    public function map($ajuan): array
    {
        $this->rowNumber++;
        $layananNama = $ajuan->layanan ? $ajuan->layanan->layanan_nama : $ajuan->ajuan_layanan_kode;
        $pelaporName = $ajuan->pelapor ? ($ajuan->pelapor->fullname ?? $ajuan->pelapor->name ?? 'Unknown') : 'Unknown';

        // Construct a descriptive pelapor string based on PRD
        $pelaporType = $ajuan->isOnline() ? 'Online' : 'Offline';
        $pelaporTipe = $ajuan->isMandiri() ? 'Mandiri' : 'Operator';
        $pelaporStr = sprintf('%s (%s - %s)', $pelaporName, $pelaporType, $pelaporTipe);
        
        $tanggal = $ajuan->ajuan_create_datetime ? $ajuan->ajuan_create_datetime->format('d-m-Y H:i:s') : '';

        if ($this->kategori === 'lembar_kerja') {
            $lembarKerja = $ajuan->lembarKerjas->first();
            
            return [
                $this->rowNumber,
                $ajuan->ajuan_no_reg,
                $lembarKerja ? (string) $lembarKerja->lk_ajuan_id : (string) $ajuan->ajuan_id,
                $lembarKerja ? (string) $lembarKerja->lk_produk_id : '',
                $pelaporType,
                $pelaporStr,
                $lembarKerja ? $lembarKerja->lk_status : $ajuan->ajuan_status,
                $lembarKerja && $lembarKerja->lk_create_datetime ? $lembarKerja->lk_create_datetime->format('d-m-Y H:i:s') : $tanggal,
                $ajuan->ajuan_kecamatan_name,
            ];
        }

        if ($this->kategori === 'produk') {
            $produk = $ajuan->produks->first();
            
            $namaIdentitas = '-';
            $detailModel = $ajuan->getDetailData();
            if ($detailModel) {
                $namaIdentitas = $detailModel->ajakel_nama_bayi 
                              ?? $detailModel->ajakem_nama_jenazah 
                              ?? $detailModel->ajd_nama_lengkap 
                              ?? $detailModel->ajkia_nama_lengkap 
                              ?? $detailModel->ajkk_nama_kepala_keluarga 
                              ?? $detailModel->ajktpel_nama_lengkap 
                              ?? $detailModel->ajp_nama_lengkap 
                              ?? $detailModel->ajrj_nama_lengkap 
                              ?? $detailModel->ajud_nama_lengkap 
                              ?? '-';
            }
            if ($namaIdentitas === '-' && $produk && $produk->prod_nama) {
                $namaIdentitas = $produk->prod_nama;
            }

            return [
                $this->rowNumber,
                $ajuan->ajuan_no_reg,
                $produk ? (string) $produk->prod_ajuan_id : (string) $ajuan->ajuan_id,
                $produk ? (string) $produk->prod_nomor : '',
                $namaIdentitas,
                $produk ? $produk->prod_status : $ajuan->ajuan_status,
                $produk && $produk->prod_create_datetime ? $produk->prod_create_datetime->format('d-m-Y H:i:s') : $tanggal,
                $ajuan->ajuan_kecamatan_name,
            ];
        }

        // default: Ajuan
        return [
            $this->rowNumber,
            $ajuan->ajuan_no_reg,
            $ajuan->ajuan_layanan_kode,
            $layananNama,
            $pelaporType,
            $pelaporStr,
            $ajuan->ajuan_status,
            $tanggal,
            $ajuan->ajuan_kecamatan_name,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
