<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Announcement
 *
 * @property int $announcement_id
 * @property string|null $announcement_title
 * @property int $announcement_author_id
 * @property string|null $announcement_author_fullname
 * @property string|null $announcement_type
 * @property string|null $announcement_content
 * @property string|null $announcement_status
 * @property array|null $announcement_extra
 * @property \Illuminate\Support\Carbon|null $announcement_create_datetime
 *
 * @property-read Admin|null $author
 */
final class Announcement extends Model
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string STATUS_PUBLISH = 'publish';
    public const string STATUS_DRAFT = 'draft';
    public const string STATUS_TRASH = 'trash';

    public const string TYPE_USER = 'user';
    public const string TYPE_ADMIN = 'admin';

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'announcement';

    protected $primaryKey = 'announcement_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'announcement_title',
        'announcement_author_id',
        'announcement_author_fullname',
        'announcement_type',
        'announcement_content',
        'announcement_status',
        'announcement_extra',
        'announcement_create_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'announcement_author_id'        => 'integer',
        'announcement_extra'            => 'array',
        'announcement_create_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'announcement_author_id', 'id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter published announcements.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('announcement_status', self::STATUS_PUBLISH);
    }

    /**
     * Scope to filter draft announcements.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('announcement_status', self::STATUS_DRAFT);
    }

    /**
     * Scope to filter by type (user/admin).
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('announcement_type', $type);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('announcement_create_datetime');
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->announcement_status === self::STATUS_PUBLISH;
    }

    public function isDraft(): bool
    {
        return $this->announcement_status === self::STATUS_DRAFT;
    }

    public function isTrashed(): bool
    {
        return $this->announcement_status === self::STATUS_TRASH;
    }
}
