<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\DashboardFilter;
use App\Models\Ajuan;
use App\Models\AjuanReview;
use Illuminate\Support\Facades\DB;

final class DashboardService
{
    public function __construct(
        protected DashboardFilter $filter
    ) {
    }

    public function getDashboard(
        array $filters
    ): array {

        return [
            'total_pengajuan' => $this->totalPengajuan($filters),

            'total_selesai' => $this->totalSelesai($filters),

            'total_ditolak' => $this->totalDitolak($filters),

            'rata_rata_kepuasan' => $this->rataRataKepuasan(),

            'ajuan_bulanan' => $this->ajuanBulanan($filters),

            'distribusi_wilayah' => $this->distribusiWilayah($filters),
        ];
    }

    private function totalPengajuan(
        array $filters
    ): int {

        $query = Ajuan::query();

        return $this->filter
            ->apply($query, $filters)
            ->count();
    }

    private function totalSelesai(
        array $filters
    ): int {

        $query = Ajuan::query()
            ->where('ajuan_status', 'SELESAI');

        return $this->filter
            ->apply($query, $filters)
            ->count();
    }

    private function totalDitolak(
        array $filters
    ): int {

        $query = Ajuan::query()
            ->where('ajuan_status', 'DITOLAK');

        return $this->filter
            ->apply($query, $filters)
            ->count();
    }

    private function rataRataKepuasan(): int
    {
        $avg = (float) AjuanReview::avg(
            'review_rating'
        );

        return (int) round(
            ($avg / 5) * 100
        );
    }

    private function ajuanBulanan(
        array $filters
    ): array {

        $bulan = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $result = [];

        foreach ($bulan as $index => $label) {

            $query = Ajuan::query();

            $query = $this->filter
                ->apply($query, $filters);

            $query->whereMonth(
                'ajuan_create_datetime',
                $index
            );

            $result[] = [
                'label' => $label,

                'belum_diverifikasi' => (clone $query)
                    ->where('ajuan_status', 'BELUM_DIVERIFIKASI')
                    ->count(),

                'diverifikasi' => (clone $query)
                    ->where('ajuan_status', 'DIVERIFIKASI')
                    ->count(),

                'diproses' => (clone $query)
                    ->where('ajuan_status', 'DIPROSES')
                    ->count(),

                'disetujui' => (clone $query)
                    ->where('ajuan_status', 'DISETUJUI')
                    ->count(),

                'ditolak' => (clone $query)
                    ->where('ajuan_status', 'DITOLAK')
                    ->count(),

                'selesai' => (clone $query)
                    ->where('ajuan_status', 'SELESAI')
                    ->count(),
            ];
        }

        return $result;
    }

    private function distribusiWilayah(
        array $filters
    ): array {

        $query = Ajuan::query();

        $query = $this->filter
            ->apply($query, $filters);

        $total = (clone $query)->count();

        return $query
            ->selectRaw("
                ajuan_kecamatan_name as kecamatan,
                COUNT(*) as total_ajuan
            ")
            ->groupBy(
                'ajuan_kecamatan_name'
            )
            ->orderByDesc(
                'total_ajuan'
            )
            ->limit(5)
            ->get()
            ->values()
            ->map(
                function ($item, $index) use ($total) {

                    return [
                        'id' => $index + 1,

                        'label' => $item->kecamatan,

                        'value' => $total > 0
                            ? round(
                                (
                                    $item->total_ajuan
                                    / $total
                                ) * 100
                            )
                            : 0,
                    ];
                }
            )
            ->toArray();
    }
}