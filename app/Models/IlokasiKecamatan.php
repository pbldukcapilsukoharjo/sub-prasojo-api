<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\IlokasiKecamatan
 *
 * @property int $kecamatan_id
 * @property int $kecamatan_kabupaten_id
 * @property string|null $kecamatan_kabupaten_name
 * @property string|null $kecamatan_kabupaten_code
 * @property string|null $kecamatan_name
 * @property string|null $kecamatan_code
 * @property string|null $kecamatan_code_bps
 *
 * @property-read IlokasiKabupaten|null $kabupaten
 * @property-read \Illuminate\Database\Eloquent\Collection<int, IlokasiDesa> $desas
 */
final class IlokasiKecamatan extends Model
{
    protected $table = 'ilokasi_kecamatan';

    protected $primaryKey = 'kecamatan_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kecamatan_kabupaten_id',
        'kecamatan_kabupaten_name',
        'kecamatan_kabupaten_code',
        'kecamatan_name',
        'kecamatan_code',
        'kecamatan_code_bps',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'kecamatan_kabupaten_id' => 'integer',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(IlokasiKabupaten::class, 'kecamatan_kabupaten_id', 'kabupaten_id');
    }

    public function desas(): HasMany
    {
        return $this->hasMany(IlokasiDesa::class, 'desa_kecamatan_id', 'kecamatan_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by kabupaten.
     */
    public function scopeByKabupaten(Builder $query, int $kabupatenId): Builder
    {
        return $query->where('kecamatan_kabupaten_id', $kabupatenId);
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('kecamatan_code', $code);
    }

    /**
     * Scope to search by name.
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('kecamatan_name', 'like', "%{$keyword}%");
    }
}
