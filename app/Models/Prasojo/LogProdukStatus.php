<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LogProdukStatus
 *
 * @property int $log_id
 * @property int $log_produk_id
 * @property int $log_ajuan_id
 * @property string|null $log_ajuan_no_reg
 * @property string|null $log_status
 * @property string|null $log_layanan_kode
 * @property int $log_admin_id
 * @property int $log_pelapor_id
 * @property string|null $log_note
 * @property array|null $log_extra
 * @property \Illuminate\Support\Carbon|null $log_create_datetime
 *
 * @property-read Produk|null $produk
 * @property-read Ajuan|null $ajuan
 * @property-read Admin|null $admin
 * @property-read User|null $pelapor
 */
final class LogProdukStatus extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'log_produk_status';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'log_produk_id',
        'log_ajuan_id',
        'log_ajuan_no_reg',
        'log_status',
        'log_layanan_kode',
        'log_admin_id',
        'log_pelapor_id',
        'log_note',
        'log_extra',
        'log_create_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'log_produk_id'        => 'integer',
        'log_ajuan_id'         => 'integer',
        'log_admin_id'         => 'integer',
        'log_pelapor_id'       => 'integer',
        'log_extra'            => 'array',
        'log_create_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'log_produk_id', 'prod_id');
    }

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'log_ajuan_id', 'ajuan_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'log_admin_id', 'id');
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'log_pelapor_id', 'id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('log_status', $status);
    }

    /**
     * Scope to filter by layanan kode.
     */
    public function scopeByLayanan(Builder $query, string $kode): Builder
    {
        return $query->where('log_layanan_kode', $kode);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('log_create_datetime');
    }
}
