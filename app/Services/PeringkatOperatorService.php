<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use App\Models\Ajuan;
use Carbon\Carbon;
use App\Filters\PeringkatOperatorFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

final class PeringkatOperatorService
{
    /**
     * @param array $filters
     * @return array
     */
    public function index(array $filters): array
    {
        $perPage = 5;

        // KODE MILIK FALAH (dengan polesan Amru: Mencegah N+1 dengan Query Aggregation)
        // Falah sebelumnya melakukan loop pada seluruh relasi logAjuanStatuses dan ajuan di PHP.
        // Polesan Amru: Mengubahnya menjadi query database terpisah untuk agregasi global dan ranking.

        /*
        |--------------------------------------------------------------------------
        | Ringkasan Dashboard (Global KPI)
        |--------------------------------------------------------------------------
        */
        $globalQuery = Admin::query()
            ->where('admin.level', Admin::LEVEL_OPERATOR)
            ->leftJoin('log_ajuan_status', 'admin.id', '=', 'log_ajuan_status.log_admin_id')
            ->leftJoin('ajuan', 'log_ajuan_status.log_ajuan_id', '=', 'ajuan.ajuan_id');

        PeringkatOperatorFilter::apply($globalQuery, $filters);

        $globalStats = $globalQuery->select(
            DB::raw('COUNT(log_ajuan_status.log_id) as total_layanan'),
            DB::raw("SUM(CASE WHEN UPPER(log_ajuan_status.log_status) = 'SELESAI' THEN 1 ELSE 0 END) as total_selesai"),
            DB::raw("AVG(CASE WHEN UPPER(log_ajuan_status.log_status) = 'SELESAI' THEN TIMESTAMPDIFF(MINUTE, ajuan.ajuan_create_datetime, log_ajuan_status.log_create_datetime) ELSE NULL END) as rata_rata_durasi_menit")
        )->first();

        $totalLayanan = (int) ($globalStats->total_layanan ?? 0);
        $totalSelesai = (int) ($globalStats->total_selesai ?? 0);
        
        // Falah menghitung durasi dalam hitungan jam (dibagi 60)
        $rataRataDurasiMenit = (float) ($globalStats->rata_rata_durasi_menit ?? 0);
        $rataRataDurasi = $totalLayanan > 0 ? round($rataRataDurasiMenit / 60, 1) : 0;

        $tingkatSelesai = $totalLayanan > 0
            ? round(($totalSelesai / $totalLayanan) * 100, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Ranking Operator
        |--------------------------------------------------------------------------
        */
        $rankingQuery = Admin::query()
            ->where('admin.level', Admin::LEVEL_OPERATOR)
            ->leftJoin('log_ajuan_status', 'admin.id', '=', 'log_ajuan_status.log_admin_id')
            ->leftJoin('ajuan', 'log_ajuan_status.log_ajuan_id', '=', 'ajuan.ajuan_id');

        PeringkatOperatorFilter::apply($rankingQuery, $filters);

        // KODE MILIK FALAH: Sorting berdasarkan jumlah ajuan (terbanyak/tersedikit)
        $sortBy = $filters['sortBy'] ?? 'newest';
        $sortDirection = $sortBy === 'oldest' ? 'asc' : 'desc';

        $rankingQuery->select(
            'admin.id',
            'admin.fullname as operator',
            // Kita asumsikan operator berada di kelurahan/kecamatan tertentu, 
            // Falah mengambil dari log pertama, di sini kita gunakan max(kelurahan) sebagai pendekatan query yang aman
            DB::raw('MAX(ajuan.ajuan_kelurahan_name) as desa'),
            DB::raw('MAX(ajuan.ajuan_kecamatan_name) as kecamatan'),
            DB::raw('COUNT(log_ajuan_status.log_id) as jumlah_ajuan')
        )
        ->groupBy('admin.id', 'admin.fullname')
        ->orderBy('jumlah_ajuan', $sortDirection);

        $page = (int) ($filters['page'] ?? 1);
        $paginator = $rankingQuery->paginate($perPage, ['*'], 'page', $page);

        // KODE MILIK FALAH: Tambahkan nomor peringkat
        // Polesan Amru: Peringkat dihitung berdasarkan offset paginasi
        $offset = ($page - 1) * $perPage;
        
        $list = collect($paginator->items())->map(function ($item, $index) use ($offset) {
            return [
                'id' => $item->id,
                'operator' => $item->operator,
                'desa' => $item->desa ?? '-',
                'kecamatan' => $item->kecamatan ?? '-',
                'jumlah_ajuan' => $item->jumlah_ajuan,
                'peringkat' => $offset + $index + 1,
            ];
        })->values();

        /*
        |--------------------------------------------------------------------------
        | Return List (Sesuai format Falah)
        |--------------------------------------------------------------------------
        */
        return [
            'total_layanan' => $totalLayanan,
            'rata_rata_durasi' => $rataRataDurasi,
            'tingkat_selesai' => $tingkatSelesai,
            'peringkat_operator' => [
                'list' => $list,
                'meta' => [
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'total_page' => $paginator->lastPage()
                ]
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Detail Operator
    |--------------------------------------------------------------------------
    */
    public function show(int $operatorId): array 
    {
        // KODE MILIK FALAH (dengan polesan Amru: Menghindari lazy loading N+1)
        $operator = Admin::query()
            ->where('level', Admin::LEVEL_OPERATOR)
            ->findOrFail($operatorId);

        // Agregasi total menggunakan query untuk efisiensi memory
        $stats = DB::table('log_ajuan_status')
            ->where('log_admin_id', $operatorId)
            ->select(
                DB::raw('COUNT(log_id) as total_ajuan'),
                DB::raw("SUM(CASE WHEN UPPER(log_status) = 'SELESAI' THEN 1 ELSE 0 END) as total_selesai")
            )
            ->first();

        $totalAjuan = (int) ($stats->total_ajuan ?? 0);
        $totalSelesai = (int) ($stats->total_selesai ?? 0);
        $tingkatSelesai = $totalAjuan > 0 ? (int) round(($totalSelesai / $totalAjuan) * 100) : 0;

        /*
        |--------------------------------------------------------------------------
        | Layanan per Bulan
        |--------------------------------------------------------------------------
        */
        $bulan = [
            'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0,
            'Mei' => 0, 'Jun' => 0, 'Jul' => 0, 'Agu' => 0,
            'Sep' => 0, 'Okt' => 0, 'Nov' => 0, 'Des' => 0,
        ];
        
        $monthsMap = array_keys($bulan);

        $chartQuery = DB::table('log_ajuan_status')
            ->where('log_admin_id', $operatorId)
            ->whereNotNull('log_create_datetime')
            ->select(
                DB::raw('MONTH(log_create_datetime) as bulan_num'),
                DB::raw('COUNT(log_id) as total')
            )
            ->groupBy('bulan_num')
            ->get();

        foreach ($chartQuery as $item) {
            if ($item->bulan_num >= 1 && $item->bulan_num <= 12) {
                $bulan[$monthsMap[$item->bulan_num - 1]] = (int) $item->total;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Riwayat Layanan
        |--------------------------------------------------------------------------
        */
        // Falah mengambil seluruh riwayat, kita limit untuk mencegah memory overflow, 
        // atau paginasi di kemudian hari. Sementara kita sesuaikan logic Falah dengan eager loading
        $logs = $operator->logAjuanStatuses()->with('ajuan.pelapor')->orderByDesc('log_create_datetime')->get();

        $riwayat = [];

        foreach ($logs as $log) {
            if (!$log->ajuan) {
                continue;
            }

            $ajuan = $log->ajuan;

            $riwayat[] = [
                'id' => $log->log_id,
                'no_regis' => $ajuan->ajuan_no_reg,
                'pemohon' => $ajuan->pelapor?->fullname,
                'kode_ajuan' => $ajuan->ajuan_layanan_kode,
                'desa' => $ajuan->ajuan_kelurahan_name,
                'tanggal' => $log->log_create_datetime ? Carbon::parse($log->log_create_datetime)->format('d-m-Y') : null,
                'waktu' => $log->log_create_datetime ? Carbon::parse($log->log_create_datetime)->format('H:i') : null,
                'status' => $log->log_status,
            ];
        }

        return [
            'id' => $operator->id,
            'nama' => $operator->fullname,
            'total_ajuan' => $totalAjuan,
            'total_selesai' => $totalSelesai,
            'tingkat_selesai' => $tingkatSelesai,
            'layanan_perbulan' => $bulan,
            'riwayat_layanan' => $riwayat, // Sudah tersortir dari database DESC
        ];
    }
}