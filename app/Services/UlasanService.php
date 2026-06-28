<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\UlasanFilter;
use App\Models\AjuanReview;
use Illuminate\Database\Eloquent\Builder;

final class UlasanService
{
    /**
     * Jumlah data per halaman.
     */
    private const int PER_PAGE = 10;

    /**
     * Daftar ulasan.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function index(array $filters): array
    {
        $query = AjuanReview::query()
            ->with([
                'ajuan.layanan',
            ]);

        UlasanFilter::apply(
            $query,
            $filters
        );

        $reviews = (clone $query)->paginate(
            self::PER_PAGE,
            ['*'],
            'page',
            (int) ($filters['page'] ?? 1)
        );

        return [
            'summary' => $this->summary(clone $query),
            'reviews' => $reviews,
        ];
    }

    /**
     * KPI Ulasan.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function kpi(array $filters = []): array
    {
        $query = AjuanReview::query();

        UlasanFilter::apply(
            $query,
            $filters
        );

        return $this->summary($query);
    }

    /**
     * Ringkasan ulasan.
     *
     * @return array<string,mixed>
     */
    private function summary(
        Builder $query
    ): array {

        $average = round(
            (float) (clone $query)->reorder()->avg('review_rating'),
            1
        );

        $totalReview = (clone $query)
            ->reorder()
            ->count();

        $rating = (clone $query)
            ->reorder()
            ->selectRaw('review_rating, COUNT(*) AS total')
            ->groupBy('review_rating')
            ->pluck('total', 'review_rating');

        return [
            'average_rating' => $average,
            'total_review' => $totalReview,
            'rating' => [
                1 => (int) ($rating[1] ?? 0),
                2 => (int) ($rating[2] ?? 0),
                3 => (int) ($rating[3] ?? 0),
                4 => (int) ($rating[4] ?? 0),
                5 => (int) ($rating[5] ?? 0),
            ],
        ];
    }
}