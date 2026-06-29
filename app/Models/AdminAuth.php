<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AdminAuth
 *
 * @property int $auth_id
 * @property int $auth_admin_id
 * @property string|null $auth_token
 * @property \Illuminate\Support\Carbon|null $auth_create_datetime
 * @property \Illuminate\Support\Carbon|null $auth_expire_datetime
 * @property array|null $auth_extra
 *
 * @property-read Admin|null $admin
 */
final class AdminAuth extends Model
{
    protected $table = 'admin_auth';

    protected $primaryKey = 'auth_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'auth_admin_id',
        'auth_token',
        'auth_create_datetime',
        'auth_expire_datetime',
        'auth_extra',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'auth_admin_id'        => 'integer',
        'auth_extra'           => 'array',
        'auth_create_datetime' => 'datetime',
        'auth_expire_datetime' => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'auth_admin_id', 'id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter active (non-expired) tokens.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('auth_expire_datetime', '>', now());
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('auth_create_datetime');
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->auth_expire_datetime !== null
            && $this->auth_expire_datetime->isPast();
    }
}
