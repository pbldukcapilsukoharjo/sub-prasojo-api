<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AjuanReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ReviewService
{
    public function getAll(
        array $filters
    ): LengthAwarePaginator {

        $query = AjuanReview::query();

        if (!empty($filters['search'])) {
            $query->where(
                'review_content',
                'like',
                '%' . $filters['search'] . '%'
            );
        }

        if (!empty($filters['rating'])) {
            $query->where(
                'review_rating',
                $filters['rating']
            );
        }

        return $query
            ->orderByDesc('review_create_datetime')
            ->paginate(10);
    }

    public function getDetail(
        int $reviewId
    ): AjuanReview {
        return AjuanReview::query()
            ->findOrFail($reviewId);
    }

    public function create(
        array $payload
    ): AjuanReview {

        $payload['review_create_datetime'] = now();

        return AjuanReview::create($payload);
    }

    public function update(
        int $reviewId,
        array $payload
    ): AjuanReview {

        $review = AjuanReview::query()
            ->findOrFail($reviewId);

        $review->update($payload);

        return $review->fresh();
    }

    public function delete(
        int $reviewId
    ): void {

        AjuanReview::query()
            ->findOrFail($reviewId)
            ->delete();
    }
}