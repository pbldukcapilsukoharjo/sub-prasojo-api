<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\DistribusiWilayahFilter;
use App\Models\Ajuan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class DistribusiWilayahService
{
    /**
     * Daftar distribusi wilayah.
     */
    public function index(array $filters): array
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = Ajuan::query();

        DistribusiWilayahFilter::apply(
            $query,
            $filters
        );

        $summary = $this->getSummary(clone $query);

        // Reset ORDER BY terlebih dahulu
        $query->reorder();

        $query
            ->select([
                'ajuan_kelurahan_code',
                'ajuan_kelurahan_name',
                'ajuan_kecamatan_code',
                'ajuan_kecamatan_name',
            ])
            ->selectRaw('COUNT(*) AS total_ajuan')
            ->selectRaw('MIN(ajuan_create_datetime) AS min_datetime')
            ->selectRaw('MAX(ajuan_create_datetime) AS max_datetime')
            ->selectRaw("
                SUM(CASE WHEN ajuan_layanan_kode = 'KTP' THEN 1 ELSE 0 END) AS ktp_el
            ")
            ->selectRaw("
                SUM(CASE WHEN ajuan_layanan_kode = 'KIA' THEN 1 ELSE 0 END) AS kia
            ")
            ->selectRaw("
                SUM(CASE WHEN ajuan_layanan_kode = 'AKL' THEN 1 ELSE 0 END) AS akta_kelahiran
            ")
            ->selectRaw("
                SUM(CASE WHEN ajuan_layanan_kode = 'AKM' THEN 1 ELSE 0 END) AS akta_kematian
            ")
            ->selectRaw("
                SUM(CASE WHEN ajuan_layanan_kode = 'PND' THEN 1 ELSE 0 END) AS perpindahan
            ")
            ->selectRaw("
                SUM(CASE WHEN ajuan_layanan_kode = 'DTG' THEN 1 ELSE 0 END) AS kedatangan
            ")
            ->selectRaw("
                SUM(CASE WHEN ajuan_layanan_kode = 'UPD' THEN 1 ELSE 0 END) AS update_data
            ")
            ->selectRaw("
                SUM(CASE WHEN ajuan_layanan_kode = 'RKJ' THEN 1 ELSE 0 END) AS rekam_jemput_bola
            ")
            ->groupBy(
                'ajuan_kelurahan_code',
                'ajuan_kelurahan_name',
                'ajuan_kecamatan_code',
                'ajuan_kecamatan_name'
            );

        $this->applyAggregateSorting(
            $query,
            $filters['sortBy'] ?? 'newest'
        );

        $pagination = $query->paginate(
            $perPage,
            ['*'],
            'page',
            $filters['page'] ?? 1
        );

        return [
            'summary' => $summary,
            'items' => $pagination,
        ];
    }

    /**
     * Ringkasan distribusi wilayah.
     */
    private function getSummary(
        Builder $query
    ): array {
        $totalAjuan = (clone $query)->count();

        $totalKecamatan = (clone $query)
            ->distinct('ajuan_kecamatan_code')
            ->count('ajuan_kecamatan_code');

        $totalDesa = (clone $query)
            ->distinct('ajuan_kelurahan_code')
            ->count('ajuan_kelurahan_code');

        return [
            'total_kecamatan' => $totalKecamatan,
            'total_ajuan_dokumen' => $totalAjuan,
            'rata_rata_ajuan' => $totalDesa > 0
                ? round($totalAjuan / $totalDesa)
                : 0,
        ];
    }

    /**
     * Sorting setelah GROUP BY.
     */
    private function applyAggregateSorting(
        Builder $query,
        string $sortBy
    ): void {
        // Reset ORDER BY lagi untuk memastikan tidak ada default order
        $query->reorder();

        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('min_datetime', 'asc');
                break;

            case 'most_submission':
                $query->orderByDesc('total_ajuan');
                break;

            case 'least_submission':
                $query->orderBy('total_ajuan');
                break;

            default: // 'newest'
                $query->orderByDesc('max_datetime');
                break;
        }
    }
}