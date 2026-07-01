<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\DeliveryProses
 *
 * @property int $delivery_proses_id
 * @property string|null $delivery_proses_kode
 * @property \Illuminate\Support\Carbon|null $delivery_proses_create_datetime
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Delivery> $deliveries
 */
final class DeliveryProses extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'delivery_proses';

    protected $primaryKey = 'delivery_proses_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'delivery_proses_kode',
        'delivery_proses_create_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'delivery_proses_create_datetime' => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'delivery_proses_kode', 'delivery_proses_kode');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('delivery_proses_create_datetime');
    }
}
