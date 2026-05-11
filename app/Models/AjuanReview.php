<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanReview
 *
 * @property int $review_id
 * @property int $review_ajuan_id
 * @property int $review_pelapor_id
 * @property int $review_rating
 * @property string|null $review_content
 * @property \Illuminate\Support\Carbon|null $review_create_datetime
 *
 * @property-read Ajuan|null $ajuan
 * @property-read User|null $pelapor
 */
final class AjuanReview extends Model
{
    protected $table = 'ajuan_review';

    protected $primaryKey = 'review_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'review_ajuan_id',
        'review_pelapor_id',
        'review_rating',
        'review_content',
        'review_create_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'review_ajuan_id'        => 'integer',
        'review_pelapor_id'      => 'integer',
        'review_rating'          => 'integer',
        'review_create_datetime' => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'review_ajuan_id', 'ajuan_id');
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'review_pelapor_id', 'id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by minimum rating.
     */
    public function scopeMinRating(Builder $query, int $rating): Builder
    {
        return $query->where('review_rating', '>=', $rating);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('review_create_datetime');
    }
}
