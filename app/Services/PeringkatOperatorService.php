<?php

namespace App\Services;

use App\Models\Admin;
use Carbon\Carbon;
use App\Filters\PeringkatOperatorFilter;

class PeringkatOperatorService
{
    public function index(array $filters): array
    {
        $perPage = 5;

        $query = Admin::query()
            ->with([
                'logAjuanStatuses.ajuan'
            ])
            ->where('level', Admin::LEVEL_OPERATOR);

        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */
        PeringkatOperatorFilter::apply(
            $query,
            $filters
        );

        /*
        |--------------------------------------------------------------------------
        | Ambil seluruh operator
        |--------------------------------------------------------------------------
        */
        $operators = $query->get();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan Dashboard
        |--------------------------------------------------------------------------
        */
        $totalLayanan = 0;

        $totalDurasi = 0;

        $totalSelesai = 0;

        $totalSemuaAjuan = 0;

        foreach ($operators as $operator) {

            $logs = $operator->logAjuanStatuses;

            $totalLayanan += $logs->count();

            foreach ($logs as $log) {

                if (!$log->ajuan) {
                    continue;
                }

                $ajuan = $log->ajuan;

                /*
                |--------------------------------------------------------------------------
                | Hitung durasi proses
                |--------------------------------------------------------------------------
                */
                if (
                    $ajuan->ajuan_create_datetime &&
                    $log->log_create_datetime
                ) {

                    $durasi = Carbon::parse(
                        $ajuan->ajuan_create_datetime
                    )->diffInMinutes(
                        Carbon::parse(
                            $log->log_create_datetime
                        )
                    ) / 60;

                    $totalDurasi += $durasi;
                }

                /*
                |--------------------------------------------------------------------------
                | Hitung tingkat selesai
                |--------------------------------------------------------------------------
                */
                $totalSemuaAjuan++;

                if (
                    strtoupper($log->log_status) === 'SELESAI'
                ) {
                    $totalSelesai++;
                }
            }
        }

        $rataRataDurasi =
            $totalLayanan > 0
                ? round(
                    $totalDurasi / $totalLayanan,
                    1
                )
                : 0;

        $tingkatSelesai =
            $totalSemuaAjuan > 0
                ? round(
                    ($totalSelesai / $totalSemuaAjuan) * 100,
                    1
                )
                : 0;

        /*
        |--------------------------------------------------------------------------
        | Ranking Operator
        |--------------------------------------------------------------------------
        */
        $ranking = [];

        foreach ($operators as $operator) {

            $jumlahAjuan = $operator
                ->logAjuanStatuses
                ->count();

            $desa = '-';

            $kecamatan = '-';

            $logPertama = $operator
                ->logAjuanStatuses
                ->first();

            if (
                $logPertama &&
                $logPertama->ajuan
            ) {

                $desa =
                    $logPertama
                        ->ajuan
                        ->ajuan_kelurahan_name
                    ?? '-';

                $kecamatan =
                    $logPertama
                        ->ajuan
                        ->ajuan_kecamatan_name
                    ?? '-';
            }

            $ranking[] = [

                'id' =>
                    $operator->id,

                'operator' =>
                    $operator->fullname,

                'desa' =>
                    $desa,

                'kecamatan' =>
                    $kecamatan,

                'jumlah_ajuan' =>
                    $jumlahAjuan,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting Ranking
        |--------------------------------------------------------------------------
        */
        $ranking = collect($ranking);

        if (
            ($filters['sortBy'] ?? 'newest')
            === 'oldest'
        ) {

            $ranking = $ranking
                ->sortBy('jumlah_ajuan')
                ->values();

        } else {

            $ranking = $ranking
                ->sortByDesc('jumlah_ajuan')
                ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | Nomor Peringkat
        |--------------------------------------------------------------------------
        */
        $ranking = $ranking
            ->values()
            ->map(function (
                $item,
                $index
            ) {

                $item['peringkat'] =
                    $index + 1;

                return $item;
            });

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $page = (int) (
            $filters['page'] ?? 1
        );

        $total =
            $ranking->count();

        $list = $ranking
            ->forPage(
                $page,
                $perPage
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Return List
        |--------------------------------------------------------------------------
        */
        return [

            'total_layanan' => $totalLayanan,

            'rata_rata_durasi' => $rataRataDurasi,

            'tingkat_selesai' => $tingkatSelesai,

            'peringkat_operator' => [

                'list' => $list,

                'meta' => [

                    'page' => $page,

                    'per_page' => $perPage,

                    'total' => $total,

                    'total_page' => (int) ceil(
                        $total / $perPage
                    )
                ]
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Detail Operator
    |--------------------------------------------------------------------------
    */

    public function show(
        int $operatorId
    ): array {

        $operator = Admin::query()
            ->with([
                'logAjuanStatuses.ajuan.pelapor'
            ])
            ->where(
                'level',
                Admin::LEVEL_OPERATOR
            )
            ->findOrFail($operatorId);

        $logs = $operator->logAjuanStatuses;

        $totalAjuan = $logs->count();

        $totalSelesai = $logs
            ->where(
                'log_status',
                'SELESAI'
            )
            ->count();

        $tingkatSelesai =
            $totalAjuan > 0
                ? round(
                    ($totalSelesai / $totalAjuan) * 100
                )
                : 0;

        /*
        |--------------------------------------------------------------------------
        | Layanan per Bulan
        |--------------------------------------------------------------------------
        */

        $bulan = [
            'Jan' => 0,
            'Feb' => 0,
            'Mar' => 0,
            'Apr' => 0,
            'Mei' => 0,
            'Jun' => 0,
            'Jul' => 0,
            'Agu' => 0,
            'Sep' => 0,
            'Okt' => 0,
            'Nov' => 0,
            'Des' => 0,
        ];

        foreach ($logs as $log) {

            if (!$log->log_create_datetime) {
                continue;
            }

            $index = Carbon::parse(
                $log->log_create_datetime
            )->month;

            $keys = array_keys($bulan);

            $bulan[
                $keys[$index - 1]
            ]++;
        }

        /*
        |--------------------------------------------------------------------------
        | Riwayat Layanan
        |--------------------------------------------------------------------------
        */

        $riwayat = [];

        foreach ($logs as $log) {

            if (!$log->ajuan) {
                continue;
            }

            $ajuan = $log->ajuan;

            $riwayat[] = [

                'id' => $log->log_id,

                'no_regis' =>
                    $ajuan->ajuan_no_reg,

                'pemohon' =>
                    $ajuan->pelapor?->fullname,

                'kode_ajuan' =>
                    $ajuan->ajuan_layanan_kode,

                'desa' =>
                    $ajuan->ajuan_kelurahan_name,

                'tanggal' =>
                    optional(
                        $log->log_create_datetime
                    )->format('d-m-Y'),

                'waktu' =>
                    optional(
                        $log->log_create_datetime
                    )->format('H:i'),

                'status' =>
                    $log->log_status,
            ];
        }

        return [

            'id' =>
                $operator->id,

            'nama' =>
                $operator->fullname,

            'total_ajuan' =>
                $totalAjuan,

            'total_selesai' =>
                $totalSelesai,

            'tingkat_selesai' =>
                $tingkatSelesai,

            'layanan_perbulan' =>
                $bulan,

            'riwayat_layanan' =>
                collect($riwayat)
                    ->sortByDesc('tanggal')
                    ->values(),
        ];
    }
}