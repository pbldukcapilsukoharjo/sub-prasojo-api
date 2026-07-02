<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserRegisterVerifyData
 *
 * @property int $rvd_id
 * @property string $rvd_status
 * @property string|null $rvd_nik
 * @property string|null $rvd_fullname
 * @property string|null $rvd_kk
 * @property string|null $rvd_email
 * @property string|null $rvd_phone
 * @property array|null $rvd_userdata
 * @property string|null $rvd_token
 * @property string|null $rvd_note
 * @property \Illuminate\Support\Carbon|null $rvd_create_datetime
 * @property \Illuminate\Support\Carbon|null $rvd_update_datetime
 */
final class UserRegisterVerifyData extends Model
{
    protected $connection = 'mysql_prasojo';

    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string STATUS_PENGAJUAN = 'PENGAJUAN';
    public const string STATUS_BELUM_DIVERIFIKASI = 'BELUM DIVERIFIKASI';
    public const string STATUS_DISETUJUI = 'DISETUJUI';
    public const string STATUS_DITOLAK = 'DITOLAK';

    public const array STATUSES = [
        self::STATUS_PENGAJUAN,
        self::STATUS_BELUM_DIVERIFIKASI,
        self::STATUS_DISETUJUI,
        self::STATUS_DITOLAK,
    ];

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'user_register_verify_data';

    protected $primaryKey = 'rvd_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rvd_status',
        'rvd_nik',
        'rvd_fullname',
        'rvd_kk',
        'rvd_email',
        'rvd_phone',
        'rvd_userdata',
        'rvd_token',
        'rvd_note',
        'rvd_create_datetime',
        'rvd_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'rvd_userdata'        => 'array',
        'rvd_create_datetime' => 'datetime',
        'rvd_update_datetime' => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('rvd_status', $status);
    }

    /**
     * Scope to filter pending verifications.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('rvd_status', self::STATUS_PENGAJUAN);
    }

    /**
     * Scope to filter approved verifications.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('rvd_status', self::STATUS_DISETUJUI);
    }

    /**
     * Scope to filter rejected verifications.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('rvd_status', self::STATUS_DITOLAK);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('rvd_create_datetime');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function rvdNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }

    protected function rvdPhone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value,
            set: fn (?string $value): ?string => $value ? preg_replace('/[^0-9+]/', '', trim($value)) : null,
        );
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->rvd_status === self::STATUS_PENGAJUAN;
    }

    public function isApproved(): bool
    {
        return $this->rvd_status === self::STATUS_DISETUJUI;
    }

    public function isRejected(): bool
    {
        return $this->rvd_status === self::STATUS_DITOLAK;
    }
}
