<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\DeliveryItem
 *
 * @property int $delivery_item_id
 * @property int $delivery_item_pelapor_id
 * @property int $delivery_item_delivery_id
 * @property int $delivery_item_ajuan_id
 * @property int $delivery_item_prod_id
 * @property string|null $delivery_item_ajuan_no_reg
 * @property string|null $delivery_item_layanan_kode
 * @property string|null $delivery_item_prod_nomor
 * @property string|null $delivery_item_prod_nama
 * @property \Illuminate\Support\Carbon|null $delivery_item_create_datetime
 * @property \Illuminate\Support\Carbon|null $delivery_item_update_datetime
 *
 * @property-read User|null $pelapor
 * @property-read Delivery|null $delivery
 * @property-read Ajuan|null $ajuan
 * @property-read Produk|null $produk
 */
final class DeliveryItem extends Model
{
    protected $table = 'delivery_item';

    protected $primaryKey = 'delivery_item_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'delivery_item_pelapor_id',
        'delivery_item_delivery_id',
        'delivery_item_ajuan_id',
        'delivery_item_prod_id',
        'delivery_item_ajuan_no_reg',
        'delivery_item_layanan_kode',
        'delivery_item_prod_nomor',
        'delivery_item_prod_nama',
        'delivery_item_create_datetime',
        'delivery_item_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'delivery_item_pelapor_id'       => 'integer',
        'delivery_item_delivery_id'      => 'integer',
        'delivery_item_ajuan_id'         => 'integer',
        'delivery_item_prod_id'          => 'integer',
        'delivery_item_create_datetime'  => 'datetime',
        'delivery_item_update_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_item_pelapor_id', 'id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_item_delivery_id', 'delivery_id');
    }

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'delivery_item_ajuan_id', 'ajuan_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'delivery_item_prod_id', 'prod_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by layanan kode.
     */
    public function scopeByLayanan(Builder $query, string $kode): Builder
    {
        return $query->where('delivery_item_layanan_kode', $kode);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('delivery_item_create_datetime');
    }
}
