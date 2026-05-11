<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LembarKerja
 *
 * @property int $lk_id
 * @property int $lk_ajuan_id
 * @property string|null $lk_ajuan_no_reg
 * @property int $lk_jenis_ajuan_id
 * @property string|null $lk_from_layanan_kode
 * @property string|null $lk_layanan_kode
 * @property bool $lk_is_produk
 * @property bool $lk_ajuan_is_online
 * @property bool $lk_ajuan_is_mandiri
 * @property int $lk_produk_id
 * @property int $lk_pelapor_role_id
 * @property string|null $lk_pelapor_role_name
 * @property string|null $lk_status
 * @property \Illuminate\Support\Carbon|null $lk_create_datetime
 * @property \Illuminate\Support\Carbon|null $lk_update_datetime
 *
 * @property-read Ajuan|null $ajuan
 * @property-read Produk|null $produk
 */
final class LembarKerja extends Model
{
    protected $table = 'lembar_kerja';

    protected $primaryKey = 'lk_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lk_ajuan_id',
        'lk_ajuan_no_reg',
        'lk_jenis_ajuan_id',
        'lk_from_layanan_kode',
        'lk_layanan_kode',
        'lk_is_produk',
        'lk_ajuan_is_online',
        'lk_ajuan_is_mandiri',
        'lk_produk_id',
        'lk_pelapor_role_id',
        'lk_pelapor_role_name',
        'lk_status',
        'lk_create_datetime',
        'lk_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'lk_ajuan_id'         => 'integer',
        'lk_jenis_ajuan_id'   => 'integer',
        'lk_is_produk'        => 'boolean',
        'lk_ajuan_is_online'  => 'boolean',
        'lk_ajuan_is_mandiri' => 'boolean',
        'lk_produk_id'        => 'integer',
        'lk_pelapor_role_id'  => 'integer',
        'lk_create_datetime'  => 'datetime',
        'lk_update_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'lk_ajuan_id', 'ajuan_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'lk_produk_id', 'prod_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('lk_status', $status);
    }

    /**
     * Scope to filter by layanan kode.
     */
    public function scopeByLayanan(Builder $query, string $kode): Builder
    {
        return $query->where('lk_layanan_kode', $kode);
    }

    /**
     * Scope to filter online.
     */
    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('lk_ajuan_is_online', true);
    }

    /**
     * Scope to filter mandiri.
     */
    public function scopeMandiri(Builder $query): Builder
    {
        return $query->where('lk_ajuan_is_mandiri', true);
    }

    /**
     * Scope to filter produk worksheets.
     */
    public function scopeProduk(Builder $query): Builder
    {
        return $query->where('lk_is_produk', true);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('lk_create_datetime');
    }
}
