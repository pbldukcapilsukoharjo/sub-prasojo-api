<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Admin
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $fullname
 * @property string|null $nik
 * @property string|null $kk
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $password
 * @property string|null $image
 * @property string $level
 * @property int $role_id
 * @property bool $is_active
 * @property bool $is_verified
 * @property bool $is_verified_email
 * @property bool $is_verified_phone
 * @property string|null $kecamatan_code
 * @property string|null $kecamatan_name
 * @property string|null $kelurahan_code
 * @property string|null $kelurahan_name
 * @property string|null $dukuh
 * @property string|null $rt
 * @property string|null $rw
 * @property array|null $extra
 * @property string|null $fcm
 * @property \Illuminate\Support\Carbon|null $create_datetime
 * @property \Illuminate\Support\Carbon|null $update_datetime
 *
 * @property-read AdminRole|null $role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AdminAuth> $authTokens
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LogAjuanStatus> $logAjuanStatuses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LogProdukStatus> $logProdukStatuses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Announcement> $announcements
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LayananContent> $layananContents
 */
final class Admin extends Model
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string LEVEL_ADMINISTRATOR = 'administrator';
    public const string LEVEL_ADMIN = 'admin';
    public const string LEVEL_OPERATOR = 'operator';

    public const array LEVELS = [
        self::LEVEL_ADMINISTRATOR,
        self::LEVEL_ADMIN,
        self::LEVEL_OPERATOR,
    ];

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'admin';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'fullname',
        'nik',
        'kk',
        'email',
        'phone',
        'password',
        'image',
        'level',
        'role_id',
        'is_active',
        'is_verified',
        'is_verified_email',
        'is_verified_phone',
        'kecamatan_code',
        'kecamatan_name',
        'kelurahan_code',
        'kelurahan_name',
        'dukuh',
        'rt',
        'rw',
        'extra',
        'fcm',
        'create_datetime',
        'update_datetime',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active'         => 'boolean',
        'is_verified'       => 'boolean',
        'is_verified_email' => 'boolean',
        'is_verified_phone' => 'boolean',
        'extra'             => 'array',
        'role_id'           => 'integer',
        'create_datetime'   => 'datetime',
        'update_datetime'   => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'role_id', 'admin_role_id');
    }

    public function authTokens(): HasMany
    {
        return $this->hasMany(AdminAuth::class, 'auth_admin_id', 'id');
    }

    public function logAjuanStatuses(): HasMany
    {
        return $this->hasMany(LogAjuanStatus::class, 'log_admin_id', 'id');
    }

    public function logProdukStatuses(): HasMany
    {
        return $this->hasMany(LogProdukStatus::class, 'log_admin_id', 'id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'announcement_author_id', 'id');
    }

    public function layananContents(): HasMany
    {
        return $this->hasMany(LayananContent::class, 'lc_author_id', 'id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter only active admins.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter only verified admins.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to filter by level.
     */
    public function scopeByLevel(Builder $query, string $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to filter by kecamatan code.
     */
    public function scopeByKecamatan(Builder $query, string $code): Builder
    {
        return $query->where('kecamatan_code', $code);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('create_datetime');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    /**
     * Normalize NIK to exactly 16 digits.
     */
    protected function nik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }

    /**
     * Normalize phone number.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value,
            set: fn (?string $value): ?string => $value ? preg_replace('/[^0-9+]/', '', trim($value)) : null,
        );
    }

    /**
     * Uppercase level accessor.
     */
    protected function levelLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => strtoupper($this->level),
        );
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isVerified(): bool
    {
        return (bool) $this->is_verified;
    }

    public function isAdministrator(): bool
    {
        return $this->level === self::LEVEL_ADMINISTRATOR;
    }

    public function isAdmin(): bool
    {
        return $this->level === self::LEVEL_ADMIN;
    }

    public function isOperator(): bool
    {
        return $this->level === self::LEVEL_OPERATOR;
    }
}
