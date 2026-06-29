<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\IlokasiDesa
 *
 * @property int $desa_id
 * @property int $desa_kecamatan_id
 * @property string|null $desa_kecamatan_name
 * @property string|null $desa_kecamatan_code
 * @property string|null $desa_name
 * @property string|null $desa_code
 * @property string|null $desa_code_bps
 *
 * @property-read IlokasiKecamatan|null $kecamatan
 */
final class IlokasiDesa extends Model
{
    protected $table = 'ilokasi_desa';

    protected $primaryKey = 'desa_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'desa_kecamatan_id',
        'desa_kecamatan_name',
        'desa_kecamatan_code',
        'desa_name',
        'desa_code',
        'desa_code_bps',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'desa_kecamatan_id' => 'integer',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(IlokasiKecamatan::class, 'desa_kecamatan_id', 'kecamatan_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by kecamatan.
     */
    public function scopeByKecamatan(Builder $query, int $kecamatanId): Builder
    {
        return $query->where('desa_kecamatan_id', $kecamatanId);
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('desa_code', $code);
    }

    /**
     * Scope to search by name.
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('desa_name', 'like', "%{$keyword}%");
    }
}
