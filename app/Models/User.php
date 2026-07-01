<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\User
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
 * @property string|null $swafoto
 * @property string $level
 * @property int $role_id
 * @property bool $is_active
 * @property bool $is_verified
 * @property bool $is_verified_email
 * @property bool $is_verified_phone
 * @property bool $is_request_update
 * @property string|null $kecamatan_code
 * @property string|null $kecamatan_name
 * @property string|null $kelurahan_code
 * @property string|null $kelurahan_name
 * @property string|null $dukuh
 * @property string|null $rt
 * @property string|null $rw
 * @property array|null $extra
 * @property array|null $quota
 * @property string|null $fcm
 * @property string|null $role_kabupaten_name
 * @property string|null $role_kabupaten_code
 * @property string|null $role_kecamatan_name
 * @property string|null $role_kecamatan_code
 * @property string|null $role_kelurahan_name
 * @property string|null $role_kelurahan_code
 * @property \Illuminate\Support\Carbon|null $create_datetime
 * @property \Illuminate\Support\Carbon|null $update_datetime
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UserAuth> $authTokens
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ajuan> $ajuans
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Produk> $produks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Delivery> $deliveries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Bpp> $bpps
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AjuanReview> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Notification> $notifications
 */
final class User extends Authenticatable
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const LEVEL_USER = 'user';
    public const LEVEL_PERANTARA = 'perantara';

    public const LEVELS = [
        self::LEVEL_USER,
        self::LEVEL_PERANTARA,
    ];

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $connection = 'mysql_prasojo';

    protected $table = 'user';

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
        'swafoto',
        'level',
        'role_id',
        'is_active',
        'is_verified',
        'is_verified_email',
        'is_verified_phone',
        'is_request_update',
        'kecamatan_code',
        'kecamatan_name',
        'kelurahan_code',
        'kelurahan_name',
        'dukuh',
        'rt',
        'rw',
        'extra',
        'quota',
        'fcm',
        'role_kabupaten_name',
        'role_kabupaten_code',
        'role_kecamatan_name',
        'role_kecamatan_code',
        'role_kelurahan_name',
        'role_kelurahan_code',
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
        'role_id'            => 'integer',
        'is_active'          => 'boolean',
        'is_verified'        => 'boolean',
        'is_verified_email'  => 'boolean',
        'is_verified_phone'  => 'boolean',
        'is_request_update'  => 'boolean',
        'extra'              => 'array',
        'quota'              => 'array',
        'create_datetime'    => 'datetime',
        'update_datetime'    => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function authTokens(): HasMany
    {
        return $this->hasMany(UserAuth::class, 'auth_user_id', 'id');
    }

    public function ajuans(): HasMany
    {
        return $this->hasMany(Ajuan::class, 'ajuan_pelapor_id', 'id');
    }

    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'prod_pelapor_id', 'id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'delivery_pelapor_id', 'id');
    }

    public function bpps(): HasMany
    {
        return $this->hasMany(Bpp::class, 'bpp_pelapor_id', 'id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AjuanReview::class, 'review_pelapor_id', 'id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'notification_user_id', 'id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter only active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter only verified users.
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
     * Scope to filter users requesting data update.
     */
    public function scopeRequestingUpdate(Builder $query): Builder
    {
        return $query->where('is_request_update', true);
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
     * Normalize phone number (strip non-numeric except +).
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value,
            set: fn (?string $value): ?string => $value ? preg_replace('/[^0-9+]/', '', trim($value)) : null,
        );
    }

    /**
     * Build full address from component fields.
     */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $parts = array_filter([
                    $this->dukuh,
                    $this->rt ? "RT {$this->rt}" : null,
                    $this->rw ? "RW {$this->rw}" : null,
                    $this->kelurahan_name,
                    $this->kecamatan_name,
                ]);

                return implode(', ', $parts);
            },
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

    public function isPerantara(): bool
    {
        return $this->level === self::LEVEL_PERANTARA;
    }

    public function isUser(): bool
    {
        return $this->level === self::LEVEL_USER;
    }

    public function isRequestingUpdate(): bool
    {
        return (bool) $this->is_request_update;
    }
}
