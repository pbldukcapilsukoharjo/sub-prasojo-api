<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Category
 *
 * @property int $cat_id
 * @property int $cat_pos
 * @property string|null $cat_title
 * @property string|null $cat_slug
 * @property string|null $cat_content
 * @property string $cat_type
 * @property string|null $cat_image
 * @property array|null $cat_extra
 * @property bool $cat_is_active
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Post> $posts
 */
final class Category extends Model
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string TYPE_BLOG = 'blog';
    public const string TYPE_REPORT = 'report';

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'category';

    protected $primaryKey = 'cat_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cat_pos',
        'cat_title',
        'cat_slug',
        'cat_content',
        'cat_type',
        'cat_image',
        'cat_extra',
        'cat_is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'cat_pos'       => 'integer',
        'cat_is_active' => 'boolean',
        'cat_extra'     => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'post_cat_id', 'cat_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter only active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('cat_is_active', true);
    }

    /**
     * Scope to filter by type (blog/report).
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('cat_type', $type);
    }

    /**
     * Scope to order by position.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('cat_pos');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    /**
     * Auto-generate slug from title on set.
     */
    protected function catSlug(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value ? \Illuminate\Support\Str::slug($value) : null,
        );
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isActive(): bool
    {
        return (bool) $this->cat_is_active;
    }
}
