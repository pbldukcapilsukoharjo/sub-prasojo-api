<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LembarKerja
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
        return $this->belongsTo(
            Ajuan::class,
            'lk_ajuan_id',
            'ajuan_id'
        );
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(
            Produk::class,
            'lk_produk_id',
            'prod_id'
        );
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope latest data.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('lk_create_datetime');
    }
}