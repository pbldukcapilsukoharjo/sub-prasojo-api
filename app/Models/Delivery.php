<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Delivery
 *
 * @property int $delivery_id
 * @property int $delivery_pelapor_id
 * @property int $delivery_ajuan_id
 * @property string|null $delivery_ajuan_no_reg
 * @property string|null $delivery_kode
 * @property string|null $delivery_resi
 * @property string|null $delivery_proses_kode
 * @property array|null $delivery_sender
 * @property array|null $delivery_receiver
 * @property string|null $delivery_receiver_name
 * @property string|null $delivery_receiver_phone
 * @property array|null $delivery_service
 * @property string|null $delivery_status
 * @property array|null $delivery_log
 * @property \Illuminate\Support\Carbon|null $delivery_proses_datetime
 * @property \Illuminate\Support\Carbon|null $delivery_create_datetime
 * @property \Illuminate\Support\Carbon|null $delivery_update_datetime
 *
 * @property-read User|null $pelapor
 * @property-read Ajuan|null $ajuan
 * @property-read DeliveryProses|null $proses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DeliveryItem> $items
 */
final class Delivery extends Model
{
    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    public const string STATUS_REQUEST = 'REQUEST';
    public const string STATUS_DIKOREKSI = 'DIKOREKSI';
    public const string STATUS_DIPROSES = 'DIPROSES';
    public const string STATUS_DISORTIR = 'DISORTIR';
    public const string STATUS_SELESAI = 'SELESAI';
    public const string STATUS_DITOLAK = 'DITOLAK';

    public const array STATUSES = [
        self::STATUS_REQUEST,
        self::STATUS_DIKOREKSI,
        self::STATUS_DIPROSES,
        self::STATUS_DISORTIR,
        self::STATUS_SELESAI,
        self::STATUS_DITOLAK,
    ];

    // ──────────────────────────────────────────────
    // Table Configuration
    // ──────────────────────────────────────────────

    protected $table = 'delivery';

    protected $primaryKey = 'delivery_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'delivery_pelapor_id',
        'delivery_ajuan_id',
        'delivery_ajuan_no_reg',
        'delivery_kode',
        'delivery_resi',
        'delivery_proses_kode',
        'delivery_sender',
        'delivery_receiver',
        'delivery_receiver_name',
        'delivery_receiver_phone',
        'delivery_service',
        'delivery_status',
        'delivery_log',
        'delivery_proses_datetime',
        'delivery_create_datetime',
        'delivery_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'delivery_pelapor_id'       => 'integer',
        'delivery_ajuan_id'         => 'integer',
        'delivery_sender'           => 'array',
        'delivery_receiver'         => 'array',
        'delivery_service'          => 'array',
        'delivery_log'              => 'array',
        'delivery_proses_datetime'  => 'datetime',
        'delivery_create_datetime'  => 'datetime',
        'delivery_update_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_pelapor_id', 'id');
    }

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'delivery_ajuan_id', 'ajuan_id');
    }

    public function proses(): BelongsTo
    {
        return $this->belongsTo(DeliveryProses::class, 'delivery_proses_kode', 'delivery_proses_kode');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class, 'delivery_item_delivery_id', 'delivery_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('delivery_status', $status);
    }

    /**
     * Scope to order by latest creation.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('delivery_create_datetime');
    }

    /**
     * Scope to filter deliveries with request status.
     */
    public function scopeRequested(Builder $query): Builder
    {
        return $query->where('delivery_status', self::STATUS_REQUEST);
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isRequest(): bool
    {
        return $this->delivery_status === self::STATUS_REQUEST;
    }

    public function isDiproses(): bool
    {
        return $this->delivery_status === self::STATUS_DIPROSES;
    }

    public function isSelesai(): bool
    {
        return $this->delivery_status === self::STATUS_SELESAI;
    }

    public function isDitolak(): bool
    {
        return $this->delivery_status === self::STATUS_DITOLAK;
    }

    public function isDikoreksi(): bool
    {
        return $this->delivery_status === self::STATUS_DIKOREKSI;
    }

    public function isDisortir(): bool
    {
        return $this->delivery_status === self::STATUS_DISORTIR;
    }
}
