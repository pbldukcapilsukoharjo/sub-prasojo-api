<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\MasterDataDukung
 *
 * @property int $mdadu_id
 * @property string|null $mdadu_layanan_kode
 * @property string|null $mdadu_judul
 * @property string|null $mdadu_desc
 * @property string|null $mdadu_image
 * @property bool $mdadu_is_required
 * @property array|null $mdadu_extra
 * @property \Illuminate\Support\Carbon|null $mdadu_create_datetime
 *
 * @property-read Layanan|null $layanan
 */
final class MasterDataDukung extends Model
{
    protected $table = 'master_data_dukung';

    protected $primaryKey = 'mdadu_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'mdadu_layanan_kode',
        'mdadu_judul',
        'mdadu_desc',
        'mdadu_image',
        'mdadu_is_required',
        'mdadu_extra',
        'mdadu_create_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'mdadu_is_required'      => 'boolean',
        'mdadu_extra'            => 'array',
        'mdadu_create_datetime'  => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'mdadu_layanan_kode', 'layanan_kode');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter required documents.
     */
    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('mdadu_is_required', true);
    }

    /**
     * Scope to filter by layanan kode.
     */
    public function scopeByLayanan(Builder $query, string $kode): Builder
    {
        return $query->where('mdadu_layanan_kode', $kode);
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isRequired(): bool
    {
        return (bool) $this->mdadu_is_required;
    }
}
