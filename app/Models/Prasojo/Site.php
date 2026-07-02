<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Site
 *
 * @property int $id
 * @property int $pos
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $content
 * @property string $type
 * @property string|null $image
 * @property array|null $extra
 * @property string $status
 */
final class Site extends Model
{
    protected $connection = 'mysql_prasojo';

    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string STATUS_PUBLISH = 'publish';
    public const string STATUS_DRAFT = 'draft';
    public const string STATUS_TRASH = 'trash';

    public const string TYPE_SITE = 'site';

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'site';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'pos',
        'title',
        'slug',
        'content',
        'type',
        'image',
        'extra',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'pos'   => 'integer',
        'extra' => 'array',
    ];

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter published pages.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISH);
    }

    /**
     * Scope to filter draft pages.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to order by position.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('pos');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    /**
     * Auto-generate slug from title on set.
     */
    protected function slug(): Attribute
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
        return $this->status === self::STATUS_PUBLISH;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isTrashed(): bool
    {
        return $this->status === self::STATUS_TRASH;
    }
}
