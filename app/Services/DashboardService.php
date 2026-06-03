<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ajuan;
use App\Models\LogAjuanStatus;
use App\Models\Produk;
use App\Models\Ulasan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

final class DashboardService
{
    /**
     * Main Dashboard Data
     */
    public function getDashboard(array $filters = []): array
    {
        $startDate = $filters['startDate'] ?? null;
        $endDate = $filters['endDate'] ?? null;

        return [
            'total_pengajuan' => $this->getTotalPengajuan($startDate, $endDate),
            'total_selesai' => $this->getTotalSelesai($startDate, $endDate),
            'total_ditolak' => $this->getTotalDitolak($startDate, $endDate),
            'label_tamat' => $this->getLabelTamat($startDate, $endDate),
            'ajuan_bulanan' => $this->getAjuanBulanan($startDate, $endDate),
            'peringkat_operator' => $this->getPeringkatOperator($startDate, $endDate),
            'distribusi_wilayah' => $this->getDistribusiWilayah($startDate, $endDate),
            'ulasan_pengguna' => $this->getUlasanPengguna($startDate, $endDate),
            'kepatuhan_sla' => $this->getKepatuhanSla($startDate, $endDate),
            'total_produk' => $this->getTotalProduk($startDate, $endDate),
            'rata_proses_selesai' => $this->getRataProsesSelesai($startDate, $endDate),
            'ringkasan_hari_ini' => $this->getRingkasanHariIni(),
            'status_proses' => $this->getStatusProses($startDate, $endDate),
        ];
    }

    /**
     * Total Pengajuan
     */
    private function getTotalPengajuan($startDate = null, $endDate = null): int
    {
        return Ajuan::query()
            ->when($startDate, fn($q) => $q->whereDate('ajuan_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('ajuan_create_datetime', '<=', $endDate))
            ->count();
    }

    /**
     * Total Selesai
     */
    private function getTotalSelesai($startDate = null, $endDate = null): int
    {
        return Ajuan::query()
            ->where('ajuan_status', 'SELESAI')
            ->when($startDate, fn($q) => $q->whereDate('ajuan_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('ajuan_create_datetime', '<=', $endDate))
            ->count();
    }

    /**
     * Total Ditolak
     */
    private function getTotalDitolak($startDate = null, $endDate = null): int
    {
        return Ajuan::query()
            ->where('ajuan_status', 'DITOLAK')
            ->when($startDate, fn($q) => $q->whereDate('ajuan_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('ajuan_create_datetime', '<=', $endDate))
            ->count();
    }

    /**
     * Label Tamat (PADUKA/TAMAT)
     */
    private function getLabelTamat($startDate = null, $endDate = null): array
    {
        $pelapor = Ajuan::query()
            ->selectRaw('ajuan_pelapor_role_name, COUNT(*) as total')
            ->when($startDate, fn($q) => $q->whereDate('ajuan_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('ajuan_create_datetime', '<=', $endDate))
            ->groupBy('ajuan_pelapor_role_name')
            ->pluck('total', 'ajuan_pelapor_role_name')
            ->toArray();

        return [
            'paduka' => (int) ($pelapor['PADUKA'] ?? 0),
            'tamat' => (int) ($pelapor['TAMAT'] ?? 0),
        ];
    }

    /**
     * Ajuan Bulanan
     */
    private function getAjuanBulanan($startDate = null, $endDate = null): array
    {
        $query = Ajuan::query()
            ->selectRaw('
                DATE_FORMAT(ajuan_create_datetime, "%Y-%m") as bulan,
                COUNT(*) as total
            ')
            ->groupBy('bulan')
            ->orderBy('bulan');

        if ($startDate) {
            $query->whereDate('ajuan_create_datetime', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('ajuan_create_datetime', '<=', $endDate);
        }

        $results = $query->get();
        
        return [
            'labels' => $results->pluck('bulan')->toArray(),
            'data' => $results->pluck('total')->toArray(),
        ];
    }

    /**
     * Peringkat Operator
     */
    private function getPeringkatOperator($startDate = null, $endDate = null): array
    {
        return LogAjuanStatus::query()
            ->join('admin', 'admin.id', '=', 'log_ajuan_status.log_admin_id')
            ->selectRaw('
                admin.id,
                admin.fullname,
                COUNT(log_ajuan_status.log_id) as jumlah_proses
            ')
            ->when($startDate, fn($q) => $q->whereDate('log_ajuan_status.log_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('log_ajuan_status.log_create_datetime', '<=', $endDate))
            ->groupBy('admin.id', 'admin.fullname')
            ->orderByDesc('jumlah_proses')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Distribusi Wilayah
     */
    private function getDistribusiWilayah($startDate = null, $endDate = null): array
    {
        return Ajuan::query()
            ->selectRaw('
                ajuan_kecamatan_name as kecamatan,
                COUNT(*) as total_ajuan
            ')
            ->when($startDate, fn($q) => $q->whereDate('ajuan_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('ajuan_create_datetime', '<=', $endDate))
            ->groupBy('ajuan_kecamatan_name')
            ->orderByDesc('total_ajuan')
            ->get()
            ->toArray();
    }

    /**
     * Ulasan Pengguna
     */
    private function getUlasanPengguna($startDate = null, $endDate = null): array
    {
        $query = Ulasan::query()
            ->selectRaw('
                AVG(rating) as rata_rating,
                COUNT(*) as total_ulasan
            ');
        
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        $result = $query->first();
        
        // Ambil 5 ulasan terbaru
        $ulasanTerbaru = Ulasan::query()
            ->with('pengguna')
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'nama' => $item->pengguna?->fullname,
                'rating' => $item->rating,
                'komentar' => $item->komentar,
                'tanggal' => optional($item->created_at)->format('Y-m-d'),
            ]);
        
        return [
            'rata_rating' => round($result->rata_rating ?? 0, 2),
            'total_ulasan' => (int) ($result->total_ulasan ?? 0),
            'ulasan_terbaru' => $ulasanTerbaru->toArray(),
        ];
    }

    /**
     * Kepatuhan SLA
     */
    private function getKepatuhanSla($startDate = null, $endDate = null): float
    {
        $total = Ajuan::query()
            ->when($startDate, fn($q) => $q->whereDate('ajuan_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('ajuan_create_datetime', '<=', $endDate))
            ->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $tepatWaktu = Ajuan::query()
            ->where('ajuan_status', 'SELESAI')
            ->whereRaw('DATEDIFF(ajuan_update_datetime, ajuan_create_datetime) <= 7') // SLA 7 hari
            ->when($startDate, fn($q) => $q->whereDate('ajuan_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('ajuan_create_datetime', '<=', $endDate))
            ->count();
        
        return round(($tepatWaktu / $total) * 100, 2);
    }

    /**
     * Total Produk
     */
    private function getTotalProduk($startDate = null, $endDate = null): array
    {
        $query = Produk::query();
        
        if ($startDate) {
            $query->whereDate('prod_create_datetime', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('prod_create_datetime', '<=', $endDate);
        }
        
        $total = $query->count();
        
        $byStatus = Produk::query()
            ->selectRaw('prod_status, COUNT(*) as total')
            ->when($startDate, fn($q) => $q->whereDate('prod_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('prod_create_datetime', '<=', $endDate))
            ->groupBy('prod_status')
            ->pluck('total', 'prod_status')
            ->toArray();
        
        return [
            'total' => $total,
            'selesai' => (int) ($byStatus['SELESAI'] ?? 0),
            'proses' => (int) ($byStatus['PROSES'] ?? 0),
        ];
    }

    /**
     * Rata-rata Proses Selesai
     */
    private function getRataProsesSelesai($startDate = null, $endDate = null): array
    {
        $rataHari = Ajuan::query()
            ->where('ajuan_status', 'SELESAI')
            ->selectRaw('AVG(DATEDIFF(ajuan_update_datetime, ajuan_create_datetime)) as rata_hari')
            ->when($startDate, fn($q) => $q->whereDate('ajuan_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('ajuan_create_datetime', '<=', $endDate))
            ->first();
        
        return [
            'rata_hari' => round($rataHari->rata_hari ?? 0, 1),
            'satuan' => 'hari',
        ];
    }

    /**
     * Ringkasan Hari Ini
     */
    private function getRingkasanHariIni(): array
    {
        $today = Carbon::today();
        
        $pengajuanHariIni = Ajuan::query()
            ->whereDate('ajuan_create_datetime', $today)
            ->count();
        
        $selesaiHariIni = Ajuan::query()
            ->where('ajuan_status', 'SELESAI')
            ->whereDate('ajuan_update_datetime', $today)
            ->count();
        
        $produkHariIni = Produk::query()
            ->whereDate('prod_create_datetime', $today)
            ->count();
        
        return [
            'pengajuan_baru' => $pengajuanHariIni,
            'pengajuan_selesai' => $selesaiHariIni,
            'produk_diterbitkan' => $produkHariIni,
            'tanggal' => $today->format('Y-m-d'),
        ];
    }

    /**
     * Status Proses
     */
    private function getStatusProses($startDate = null, $endDate = null): array
    {
        $statuses = Ajuan::query()
            ->selectRaw('ajuan_status, COUNT(*) as total')
            ->when($startDate, fn($q) => $q->whereDate('ajuan_create_datetime', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('ajuan_create_datetime', '<=', $endDate))
            ->groupBy('ajuan_status')
            ->get();
        
        $result = [];
        foreach ($statuses as $status) {
            $result[$status->ajuan_status] = $status->total;
        }
        
        return $result;
    }

    /**
     * Summary Dashboard (Legacy)
     */
    public function getSummary(): array
    {
        $pelapor = Ajuan::query()
            ->selectRaw('
                ajuan_pelapor_role_name,
                COUNT(*) as total
            ')
            ->groupBy('ajuan_pelapor_role_name')
            ->pluck('total', 'ajuan_pelapor_role_name')
            ->toArray();

        return [
            'paduka' => (int) ($pelapor['PADUKA'] ?? 0),
            'tamat'  => (int) ($pelapor['TAMAT'] ?? 0),
            'total_ajuan' => Ajuan::count(),
        ];
    }
}