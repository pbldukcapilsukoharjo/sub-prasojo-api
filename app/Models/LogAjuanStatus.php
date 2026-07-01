<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LogAjuanStatus
 *
 * @property int $log_id
 * @property int $log_pelapor_id
 * @property int $log_ajuan_id
 * @property string|null $log_ajuan_no_reg
 * @property string|null $log_status
 * @property string|null $log_layanan_kode
 * @property string|null $log_note
 * @property array|null $log_extra
 * @property int $log_admin_id
 * @property \Illuminate\Support\Carbon|null $log_create_datetime
 *
 * @property-read User|null $pelapor
 * @property-read Ajuan|null $ajuan
 * @property-read Admin|null $admin
 */
final class LogAjuanStatus extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'log_ajuan_status';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'log_pelapor_id',
        'log_ajuan_id',
        'log_ajuan_no_reg',
        'log_status',
        'log_layanan_kode',
        'log_note',
        'log_extra',
        'log_admin_id',
        'log_create_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'log_pelapor_id'       => 'integer',
        'log_ajuan_id'         => 'integer',
        'log_admin_id'         => 'integer',
        'log_extra'            => 'array',
        'log_create_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'log_pelapor_id', 'id');
    }

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'log_ajuan_id', 'ajuan_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'log_admin_id', 'id');
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
