<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Layanan
 *
 * @property int $layanan_id
 * @property int $layanan_pos
 * @property int $layanan_is_layanan
 * @property bool $layanan_is_produk
 * @property string|null $layanan_nama
 * @property string|null $layanan_desc
 * @property string|null $layanan_kode
 * @property string|null $layanan_image
 * @property array|null $layanan_extra
 * @property bool $layanan_is_active
 * @property string|null $layanan_jenis_ajuan_id_list
 * @property \Illuminate\Support\Carbon|null $layanan_create_datetime
 * @property \Illuminate\Support\Carbon|null $layanan_update_datetime
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LayananContent> $contents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MasterDataDukung> $dataDukungs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ajuan> $ajuans
 */
final class Layanan extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'layanan';

    protected $primaryKey = 'layanan_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'layanan_pos',
        'layanan_is_layanan',
        'layanan_is_produk',
        'layanan_nama',
        'layanan_desc',
        'layanan_kode',
        'layanan_image',
        'layanan_extra',
        'layanan_is_active',
        'layanan_jenis_ajuan_id_list',
        'layanan_create_datetime',
        'layanan_update_datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'layanan_pos'             => 'integer',
        'layanan_is_layanan'      => 'integer',
        'layanan_is_produk'       => 'boolean',
        'layanan_is_active'       => 'boolean',
        'layanan_extra'           => 'array',
        'layanan_create_datetime' => 'datetime',
        'layanan_update_datetime' => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function contents(): HasMany
    {
        return $this->hasMany(LayananContent::class, 'lc_layanan_kode', 'layanan_kode');
    }

    public function dataDukungs(): HasMany
    {
        return $this->hasMany(MasterDataDukung::class, 'mdadu_layanan_kode', 'layanan_kode');
    }

    public function ajuans(): HasMany
    {
        return $this->hasMany(Ajuan::class, 'ajuan_layanan_kode', 'layanan_kode');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter active layanan.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('layanan_is_active', true);
    }

    /**
     * Scope to order by position.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('layanan_pos');
    }

    /**
     * Scope to filter by kode.
     */
    public function scopeByKode(Builder $query, string $kode): Builder
    {
        return $query->where('layanan_kode', $kode);
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    public function isActive(): bool
    {
        return (bool) $this->layanan_is_active;
    }

    public function isProduk(): bool
    {
        return (bool) $this->layanan_is_produk;
    }

    /**
     * Get the jenis ajuan IDs as an array.
     *
     * @return array<int, int>
     */
    public function getJenisAjuanIds(): array
    {
        if (empty($this->layanan_jenis_ajuan_id_list)) {
            return [];
        }

        return array_map('intval', explode(',', $this->layanan_jenis_ajuan_id_list));
    }
}
