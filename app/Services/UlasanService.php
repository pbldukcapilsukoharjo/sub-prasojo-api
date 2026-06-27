<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\UlasanFilter;
use App\Models\AjuanReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UlasanService
{
    /**
     * Mendapatkan seluruh data ulasan.
     */
    public function index(array $filters): array
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = AjuanReview::query()
            ->with([
                'ajuan.layanan',
            ]);

        UlasanFilter::apply(
            $query,
            $filters
        );

        $pagination = $query->paginate(
            $perPage,
            ['*'],
            'page',
            $filters['page'] ?? 1
        );

        return [
            'summary' => $this->getSummary(),
            'reviews' => $pagination,
        ];
    }

    /**
     * Ringkasan ulasan.
     */
    private function getSummary(): array
    {
        $average = round(
            (float) AjuanReview::avg('review_rating'),
            1
        );

        $totalReview = AjuanReview::count();

        $rating = AjuanReview::query()
            ->selectRaw('review_rating, COUNT(*) as total')
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