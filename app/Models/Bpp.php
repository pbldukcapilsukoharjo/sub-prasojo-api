<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Bpp
 *
 * @property int $bpp_id
 * @property string|null $bpp_no_reg
 * @property string|null $bpp_nik
 * @property string|null $bpp_nama
 * @property string|null $bpp_tempat_lahir
 * @property \Illuminate\Support\Carbon|null $bpp_tanggal_lahir
 * @property string|null $bpp_tempat_meninggal
 * @property \Illuminate\Support\Carbon|null $bpp_tanggal_meninggal
 * @property string|null $bpp_alamat
 * @property int $bpp_rt
 * @property int $bpp_rw
 * @property string|null $bpp_kecamatan_code
 * @property string|null $bpp_kecamatan_name
 * @property string|null $bpp_desa_code
 * @property string|null $bpp_desa_name
 * @property \Illuminate\Support\Carbon|null $bpp_tanggal_pemakaman
 * @property string|null $bpp_makam_kecamatan_code
 * @property string|null $bpp_makam_kecamatan_name
 * @property string|null $bpp_makam_desa_code
 * @property string|null $bpp_makam_desa_name
 * @property string|null $bpp_makam_nama
 * @property string|null $bpp_makam_kode
 * @property int $bpp_pelapor_id
 * @property string|null $bpp_pelapor_nik
 * @property string|null $bpp_pelapor_nama
 * @property string|null $bpp_keluarga_telp_nama
 * @property string|null $bpp_keluarga_telp_no
 * @property string|null $bpp_note
 * @property string|null $bpp_status
 * @property array|null $bpp_extra
 * @property \Illuminate\Support\Carbon|null $bpp_create_datetime
 * @property \Illuminate\Support\Carbon|null $bpp_update_datetime
 *
 * @property-read User|null $pelapor
 * @property-read BppTempatPemakaman|null $tempatPemakaman
 */
final class Bpp extends Model
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string STATUS_PENGAJUAN = 'PENGAJUAN';
    public const string STATUS_DIPROSES = 'DIPROSES';
    public const string STATUS_SELESAI = 'SELESAI';
    public const string STATUS_DITOLAK = 'DITOLAK';

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'bpp';

    protected $primaryKey = 'bpp_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'bpp_no_reg',
        'bpp_nik',
        'bpp_nama',
        'bpp_tempat_lahir',
        'bpp_tanggal_lahir',
        'bpp_tempat_meninggal',
        'bpp_tanggal_meninggal',
        'bpp_alamat',
        'bpp_rt',
        'bpp_rw',
        'bpp_kecamatan_code',
        'bpp_kecamatan_name',
        'bpp_desa_code',
        'bpp_desa_name',
        'bpp_tanggal_pemakaman',
        'bpp_makam_kecamatan_code',
        'bpp_makam_kecamatan_name',
        'bpp_makam_desa_code',
        'bpp_makam_desa_name',
        'bpp_makam_nama',
        'bpp_makam_kode',
        'bpp_pelapor_id',
        'bpp_pelapor_nik',
        'bpp_pelapor_nama',
        'bpp_keluarga_telp_nama',
        'bpp_keluarga_telp_no',
        'bpp_note',
        'bpp_status',
        'bpp_extra',
        'bpp_create_datetime',
        'bpp_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'bpp_rt'                 => 'integer',
        'bpp_rw'                 => 'integer',
        'bpp_pelapor_id'         => 'integer',
        'bpp_tanggal_lahir'      => 'date',
        'bpp_tanggal_meninggal'  => 'date',
        'bpp_tanggal_pemakaman'  => 'date',
        'bpp_extra'              => 'array',
        'bpp_create_datetime'    => 'datetime',
        'bpp_update_datetime'    => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bpp_pelapor_id', 'id');
    }

    public function tempatPemakaman(): BelongsTo
    {
        return $this->belongsTo(BppTempatPemakaman::class, 'bpp_makam_kode', 'bpptp_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('bpp_status', $status);
    }

    /**
     * Scope to filter by kecamatan code.
     */
    public function scopeByKecamatan(Builder $query, string $code): Builder
    {
        return $query->where('bpp_kecamatan_code', $code);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('bpp_create_datetime');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function bppNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }

    protected function bppPelaporNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isSelesai(): bool
    {
        return $this->bpp_status === self::STATUS_SELESAI;
    }

    public function isDitolak(): bool
    {
        return $this->bpp_status === self::STATUS_DITOLAK;
    }

    public function isDiproses(): bool
    {
        return $this->bpp_status === self::STATUS_DIPROSES;
    }
}
