<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\IlokasiKabupaten
 *
 * @property int $kabupaten_id
 * @property int $kabupaten_provinsi_id
 * @property string|null $kabupaten_provinsi_name
 * @property string|null $kabupaten_provinsi_code
 * @property string|null $kabupaten_name
 * @property string|null $kabupaten_code
 * @property string|null $kabupaten_code_bps
 *
 * @property-read IlokasiProvinsi|null $provinsi
 * @property-read \Illuminate\Database\Eloquent\Collection<int, IlokasiKecamatan> $kecamatans
 */
final class IlokasiKabupaten extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'ilokasi_kabupaten';

    protected $primaryKey = 'kabupaten_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kabupaten_provinsi_id',
        'kabupaten_provinsi_name',
        'kabupaten_provinsi_code',
        'kabupaten_name',
        'kabupaten_code',
        'kabupaten_code_bps',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'kabupaten_provinsi_id' => 'integer',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(IlokasiProvinsi::class, 'kabupaten_provinsi_id', 'provinsi_id');
    }

    public function kecamatans(): HasMany
    {
        return $this->hasMany(IlokasiKecamatan::class, 'kecamatan_kabupaten_id', 'kabupaten_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by provinsi.
     */
    public function scopeByProvinsi(Builder $query, int $provinsiId): Builder
    {
        return $query->where('kabupaten_provinsi_id', $provinsiId);
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('kabupaten_code', $code);
    }

    /**
     * Scope to search by name.
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('kabupaten_name', 'like', "%{$keyword}%");
    }
}
