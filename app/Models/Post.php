<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Post
 *
 * @property int $post_id
 * @property int $post_author_id
 * @property string|null $post_author_fullname
 * @property int $post_cat_id
 * @property string|null $post_cat_title
 * @property string|null $post_title
 * @property string|null $post_slug
 * @property string $post_type
 * @property string $post_status
 * @property string|null $post_content
 * @property string|null $post_image
 * @property array|null $post_extra
 * @property \Illuminate\Support\Carbon|null $post_create_datetime
 * @property \Illuminate\Support\Carbon|null $post_update_datetime
 *
 * @property-read Admin|null $author
 * @property-read Category|null $category
 */
final class Post extends Model
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string STATUS_PUBLISH = 'publish';
    public const string STATUS_DRAFT = 'draft';
    public const string STATUS_TRASH = 'trash';

    public const string TYPE_PAGE = 'page';
    public const string TYPE_BLOG = 'blog';

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'post';

    protected $primaryKey = 'post_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_author_id',
        'post_author_fullname',
        'post_cat_id',
        'post_cat_title',
        'post_title',
        'post_slug',
        'post_type',
        'post_status',
        'post_content',
        'post_image',
        'post_extra',
        'post_create_datetime',
        'post_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'post_author_id'        => 'integer',
        'post_cat_id'           => 'integer',
        'post_extra'            => 'array',
        'post_create_datetime'  => 'datetime',
        'post_update_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'post_author_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'post_cat_id', 'cat_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('post_status', self::STATUS_PUBLISH);
    }

    /**
     * Scope to filter draft posts.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('post_status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('post_status', $status);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('post_type', $type);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory(Builder $query, int $catId): Builder
    {
        return $query->where('post_cat_id', $catId);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('post_create_datetime');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    /**
     * Auto-generate slug from title on set.
     */
    protected function postSlug(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value ? \Illuminate\Support\Str::slug($value) : null,
        );
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->post_status === self::STATUS_PUBLISH;
    }

    public function isDraft(): bool
    {
        return $this->post_status === self::STATUS_DRAFT;
    }

    public function isTrashed(): bool
    {
        return $this->post_status === self::STATUS_TRASH;
    }

    public function isBlog(): bool
    {
        return $this->post_type === self::TYPE_BLOG;
    }

    public function isPage(): bool
    {
        return $this->post_type === self::TYPE_PAGE;
    }
}
