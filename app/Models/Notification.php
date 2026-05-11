<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Notification
 *
 * @property int $notification_id
 * @property int $notification_user_id
 * @property string|null $notification_title
 * @property string|null $notification_type
 * @property bool $notification_is_read
 * @property array|null $notification_extra
 * @property \Illuminate\Support\Carbon|null $notification_create_datetime
 *
 * @property-read User|null $user
 */
final class Notification extends Model
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string TYPE_AJUAN = 'ajuan';

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'notification';

    protected $primaryKey = 'notification_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'notification_user_id',
        'notification_title',
        'notification_type',
        'notification_is_read',
        'notification_extra',
        'notification_create_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'notification_user_id'          => 'integer',
        'notification_is_read'          => 'boolean',
        'notification_extra'            => 'array',
        'notification_create_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notification_user_id', 'id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter unread notifications.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('notification_is_read', false);
    }

    /**
     * Scope to filter read notifications.
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('notification_is_read', true);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('notification_create_datetime');
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isRead(): bool
    {
        return (bool) $this->notification_is_read;
    }

    public function markAsRead(): bool
    {
        return $this->update(['notification_is_read' => true]);
    }
}
