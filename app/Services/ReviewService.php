<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AjuanReview;

final class ReviewService
{
    public function index(array $filters): array
    {
        $query = AjuanReview::query()
            ->with([
                'ajuan',
                'pelapor',
            ]);

        if (!empty($filters['rating'])) {
            $query->where(
                'review_rating',
                $filters['rating']
            );
        }

        if (!empty($filters['search'])) {
            $query->where(
                'review_content',
                'like',
                '%' . $filters['search'] . '%'
            );
        }

        $reviews = $query
            ->orderByDesc('review_create_datetime')
            ->paginate(
                $filters['per_page'] ?? 10
            );

        return [
            'rekap' => [
                'rata_rata_rating' => round(
                    AjuanReview::avg('review_rating'),
                    1
                ),

                'total_ulasan' => AjuanReview::count(),

                'distribusi_rating' => [
                    '5' => AjuanReview::where(
                        'review_rating',
                        5
                    )->count(),

                    '4' => AjuanReview::where(
                        'review_rating',
                        4
                    )->count(),

                    '3' => AjuanReview::where(
                        'review_rating',
                        3
                    )->count(),

                    '2' => AjuanReview::where(
                        'review_rating',
                        2
                    )->count(),

                    '1' => AjuanReview::where(
                        'review_rating',
                        1
                    )->count(),
                ],
            ],

            'list' => $reviews,
        ];
    }

    public function show(int $reviewId): AjuanReview
    {
        return AjuanReview::query()
            ->with([
                'ajuan',
                'pelapor',
            ])
            ->findOrFail($reviewId);
    }

    public function store(array $payload): AjuanReview
    {
        $payload['review_create_datetime'] = now();

        return AjuanReview::create($payload);
    }

    public function update(
        int $reviewId,
        array $payload
    ): AjuanReview {

        $review = AjuanReview::findOrFail(
            $reviewId
        );

        $review->update($payload);

        return $review->fresh([
            'ajuan',
            'pelapor',
        ]);
    }

    public function delete(
        int $reviewId
    ): void {

        AjuanReview::findOrFail(
            $reviewId
        )->delete();
    }
}