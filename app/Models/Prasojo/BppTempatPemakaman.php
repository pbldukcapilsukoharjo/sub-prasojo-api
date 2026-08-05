<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\BppTempatPemakaman
 *
 * @property int $bpptp_id
 * @property string|null $bpptp_jenis
 * @property string|null $bpptp_nama
 * @property string|null $bpptp_alamat
 * @property string|null $bpptp_kecamatan_code
 * @property string|null $bpptp_kecamatan_name
 * @property string|null $bpptp_desa_code
 * @property string|null $bpptp_desa_name
 * @property string|null $bpptp_petugas_nama
 * @property string|null $bpptp_petugas_desa_nama
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Bpp> $bpps
 */
final class BppTempatPemakaman extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'bpp_tempat_pemakaman';

    protected $primaryKey = 'bpptp_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'bpptp_jenis',
        'bpptp_nama',
        'bpptp_alamat',
        'bpptp_kecamatan_code',
        'bpptp_kecamatan_name',
        'bpptp_desa_code',
        'bpptp_desa_name',
        'bpptp_petugas_nama',
        'bpptp_petugas_desa_nama',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function bpps(): HasMany
    {
        return $this->hasMany(Bpp::class, 'bpp_makam_kode', 'bpptp_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter by kecamatan code.
     */
    public function scopeByKecamatan(Builder $query, string $code): Builder
    {
        return $query->where('bpptp_kecamatan_code', $code);
    }

    /**
     * Scope to filter by desa code.
     */
    public function scopeByDesa(Builder $query, string $code): Builder
    {
        return $query->where('bpptp_desa_code', $code);
    }

    /**
     * Scope to filter by jenis.
     */
    public function scopeByJenis(Builder $query, string $jenis): Builder
    {
        return $query->where('bpptp_jenis', $jenis);
    }
}
