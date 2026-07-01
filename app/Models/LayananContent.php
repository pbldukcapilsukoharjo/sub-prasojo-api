<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LayananContent
 *
 * @property int $lc_id
 * @property int $lc_author_id
 * @property string|null $lc_author_fullname
 * @property string|null $lc_title
 * @property string|null $lc_slug
 * @property string $lc_type
 * @property string $lc_layanan_kode
 * @property string $lc_status
 * @property string|null $lc_content
 * @property string|null $lc_image
 * @property array|null $lc_extra
 * @property \Illuminate\Support\Carbon|null $lc_create_datetime
 * @property \Illuminate\Support\Carbon|null $lc_update_datetime
 *
 * @property-read Admin|null $author
 * @property-read Layanan|null $layanan
 */
final class LayananContent extends Model
{
    protected $connection = 'mysql_prasojo';

    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string STATUS_PUBLISH = 'publish';
    public const string STATUS_DRAFT = 'draft';
    public const string STATUS_TRASH = 'trash';

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'layanan_content';

    protected $primaryKey = 'lc_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lc_author_id',
        'lc_author_fullname',
        'lc_title',
        'lc_slug',
        'lc_type',
        'lc_layanan_kode',
        'lc_status',
        'lc_content',
        'lc_image',
        'lc_extra',
        'lc_create_datetime',
        'lc_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'lc_author_id'        => 'integer',
        'lc_extra'            => 'array',
        'lc_create_datetime'  => 'datetime',
        'lc_update_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'lc_author_id', 'id');
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'lc_layanan_kode', 'layanan_kode');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter published content.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('lc_status', self::STATUS_PUBLISH);
    }

    /**
     * Scope to filter draft content.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('lc_status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter by layanan kode.
     */
    public function scopeByLayanan(Builder $query, string $kode): Builder
    {
        return $query->where('lc_layanan_kode', $kode);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('lc_status', $status);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('lc_create_datetime');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    /**
     * Auto-generate slug from title on set.
     */
    protected function lcSlug(): Attribute
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
        return $this->lc_status === self::STATUS_PUBLISH;
    }

    public function isDraft(): bool
    {
        return $this->lc_status === self::STATUS_DRAFT;
    }

    public function isTrashed(): bool
    {
        return $this->lc_status === self::STATUS_TRASH;
    }
}
