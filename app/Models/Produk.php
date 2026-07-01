<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Produk
 *
 * @property int $prod_id
 * @property int $prod_ajuan_id
 * @property int $prod_pelapor_id
 * @property string|null $prod_ajuan_no_reg
 * @property string|null $prod_nama
 * @property string|null $prod_nomor
 * @property string|null $prod_from_layanan_kode
 * @property string|null $prod_layanan_kode
 * @property string|null $prod_status
 * @property string|null $prod_url
 * @property array|null $prod_extra
 * @property \Illuminate\Support\Carbon|null $prod_create_datetime
 * @property \Illuminate\Support\Carbon|null $prod_update_datetime
 *
 * @property-read Ajuan|null $ajuan
 * @property-read User|null $pelapor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LogProdukStatus> $logStatuses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DeliveryItem> $deliveryItems
 */
final class Produk extends Model
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string STATUS_DIPROSES = 'DIPROSES';
    public const string STATUS_SELESAI = 'SELESAI';
    public const string STATUS_DIKIRIM = 'DIKIRIM';
    public const string STATUS_DITERIMA = 'DITERIMA';

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $connection = 'mysql_prasojo';

    protected $table = 'produk';

    protected $primaryKey = 'prod_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'prod_ajuan_id',
        'prod_pelapor_id',
        'prod_ajuan_no_reg',
        'prod_nama',
        'prod_nomor',
        'prod_from_layanan_kode',
        'prod_layanan_kode',
        'prod_status',
        'prod_url',
        'prod_extra',
        'prod_create_datetime',
        'prod_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'prod_ajuan_id'         => 'integer',
        'prod_pelapor_id'       => 'integer',
        'prod_extra'            => 'array',
        'prod_create_datetime'  => 'datetime',
        'prod_update_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'prod_ajuan_id', 'ajuan_id');
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prod_pelapor_id', 'id');
    }

    public function logStatuses(): HasMany
    {
        return $this->hasMany(LogProdukStatus::class, 'log_produk_id', 'prod_id');
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(DeliveryItem::class, 'delivery_item_prod_id', 'prod_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('prod_status', $status);
    }

    /**
     * Scope to filter by layanan kode.
     */
    public function scopeByLayanan(Builder $query, string $kode): Builder
    {
        return $query->where('prod_layanan_kode', $kode);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('prod_create_datetime');
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isDiproses(): bool
    {
        return $this->prod_status === self::STATUS_DIPROSES;
    }

    public function isSelesai(): bool
    {
        return $this->prod_status === self::STATUS_SELESAI;
    }

    public function isDikirim(): bool
    {
        return $this->prod_status === self::STATUS_DIKIRIM;
    }

    public function isDiterima(): bool
    {
        return $this->prod_status === self::STATUS_DITERIMA;
    }
}
