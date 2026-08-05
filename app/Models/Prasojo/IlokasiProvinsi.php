<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\IlokasiProvinsi
 *
 * @property int $provinsi_id
 * @property string|null $provinsi_name
 * @property string|null $provinsi_code
 * @property string|null $provinsi_code_bps
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, IlokasiKabupaten> $kabupatens
 */
final class IlokasiProvinsi extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'ilokasi_provinsi';

    protected $primaryKey = 'provinsi_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provinsi_name',
        'provinsi_code',
        'provinsi_code_bps',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function kabupatens(): HasMany
    {
        return $this->hasMany(IlokasiKabupaten::class, 'kabupaten_provinsi_id', 'provinsi_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by code.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('provinsi_code', $code);
    }

    /**
     * Scope to search by name.
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where('provinsi_name', 'like', "%{$keyword}%");
    }
}
