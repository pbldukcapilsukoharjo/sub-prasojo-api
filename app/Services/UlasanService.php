<?php

namespace App\Services;

use App\Models\AjuanReview;
use App\Filters\UlasanFilter;

class UlasanService
{
    public function getAll(
        array $filters
    ): array {

        $perPage = 5;

        $query = AjuanReview::query()
            ->with([
                'ajuan.layanan'
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

        $averageRating = round(
            (float) AjuanReview::avg(
                'review_rating'
            ),
            1
        );

        $ratingCounts = AjuanReview::query()
            ->selectRaw(
                'review_rating, COUNT(*) as total'
            )
            ->groupBy('review_rating')
            ->pluck(
                'total',
                'review_rating'
            );

        $totalRating = [];

        for ($i = 1; $i <= 5; $i++) {

            $totalRating[$i] =
                (int) ($ratingCounts[$i] ?? 0);
        }

        return [

            'rata_rata_ulasan' =>
                $averageRating,

            'total_ulasan' =>
                AjuanReview::count(),

            'total_rating' =>
                $totalRating,

            'list' =>
                $pagination->items(),

            'meta' => [
                'page' =>
                    $pagination->currentPage(),

                'per_page' =>
                    $pagination->perPage(),

                'total' =>
                    $pagination->total(),

                'total_page' =>
                    $pagination->lastPage(),
            ]
        ];
    }
}