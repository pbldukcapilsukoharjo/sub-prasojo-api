<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\UlasanFilter;
use App\Models\AjuanReview;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UlasanService
{
    public function getKpi(UlasanFilter $filter): array
    {
        $cacheKey = 'ulasan:kpi:' . md5(json_encode($filter->request));

        return Cache::remember($cacheKey, 600, function () use ($filter) {
            $query = AjuanReview::query();
            $query = $filter->apply($query);

            $kpiData = (clone $query)->select(
                DB::raw('AVG(review_rating) as rata_rata_bintang'),
                DB::raw('SUM(CASE WHEN review_rating = 5 THEN 1 ELSE 0 END) as bintang_5'),
                DB::raw('SUM(CASE WHEN review_rating = 4 THEN 1 ELSE 0 END) as bintang_4'),
                DB::raw('SUM(CASE WHEN review_rating = 3 THEN 1 ELSE 0 END) as bintang_3'),
                DB::raw('SUM(CASE WHEN review_rating = 2 THEN 1 ELSE 0 END) as bintang_2'),
                DB::raw('SUM(CASE WHEN review_rating = 1 THEN 1 ELSE 0 END) as bintang_1')
            )->first();

            return [
                'rata_rata_bintang' => round((float)($kpiData->rata_rata_bintang ?? 0), 1),
                'distribusi' => [
                    'bintang_5' => (int)($kpiData->bintang_5 ?? 0),
                    'bintang_4' => (int)($kpiData->bintang_4 ?? 0),
                    'bintang_3' => (int)($kpiData->bintang_3 ?? 0),
                    'bintang_2' => (int)($kpiData->bintang_2 ?? 0),
                    'bintang_1' => (int)($kpiData->bintang_1 ?? 0),
                ]
            ];
        });
    }

    public function getList(UlasanFilter $filter)
    {
        $query = AjuanReview::query()->from('ajuan_review');
        $query = $filter->apply($query);

        $perPage = $filter->request['per_page'] ?? 10;

        $data = $query->join('ajuan', 'ajuan.ajuan_id', '=', 'ajuan_review.review_ajuan_id')
            ->leftJoin('layanan', 'layanan.layanan_kode', '=', 'ajuan.ajuan_layanan_kode')
            ->select(
                'ajuan_review.review_id as id_review',
                DB::raw('DATE(ajuan_review.review_create_datetime) as tanggal'),
                'ajuan.ajuan_no_reg as no_reg',
                'layanan.layanan_nama as layanan',
                'ajuan_review.review_rating as rating',
                'ajuan_review.review_content as komentar'
            )
            ->paginate($perPage);

        $data->getCollection()->transform(function ($item) {
            return [
                'id_review' => $item->id_review,
                'tanggal' => $item->tanggal ? date('Y-m-d', strtotime((string)$item->tanggal)) : null,
                'no_reg' => $item->no_reg,
                'layanan' => $item->layanan,
                'rating' => $item->rating,
                'komentar' => $item->komentar,
            ];
        });

        return $data;
    }

    public function getForExport(UlasanFilter $filter)
    {
        $query = AjuanReview::query()->from('ajuan_review');
        $query = $filter->apply($query);

        $data = $query->join('ajuan', 'ajuan.ajuan_id', '=', 'ajuan_review.review_ajuan_id')
            ->leftJoin('layanan', 'layanan.layanan_kode', '=', 'ajuan.ajuan_layanan_kode')
            ->select(
                'ajuan_review.review_id as id_review',
                DB::raw('DATE(ajuan_review.review_create_datetime) as tanggal'),
                'ajuan.ajuan_no_reg as no_reg',
                'layanan.layanan_nama as layanan',
                'ajuan_review.review_rating as rating',
                'ajuan_review.review_content as komentar'
            )
            ->get();

        return $data->map(function ($item) {
            return [
                'id_review' => $item->id_review,
                'tanggal' => $item->tanggal ? date('Y-m-d', strtotime((string)$item->tanggal)) : null,
                'no_reg' => $item->no_reg,
                'layanan' => $item->layanan,
                'rating' => $item->rating,
                'komentar' => $item->komentar,
            ];
        });
    }
}