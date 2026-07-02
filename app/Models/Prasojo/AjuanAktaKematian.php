<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanAktaKematian
 *
 * @property int $ajakem_id
 * @property int $ajakem_ajuan_id
 * @property int $ajakem_jenis_id
 * @property string|null $ajakem_nik
 * @property string|null $ajakem_nama_jenazah
 * @property \Illuminate\Support\Carbon|null $ajakem_tgl_kematian
 * @property string|null $ajakem_tempat_kematian
 * @property int $ajakem_anak_ke
 * @property string|null $ajakem_nama_ibu
 * @property string|null $ajakem_nama_ayah
 * @property array|null $ajakem_dokumen
 *
 * @property-read Ajuan|null $ajuan
 * @property-read JenisAjuan|null $jenisAjuan
 */
final class AjuanAktaKematian extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'ajuan_akta_kematian';

    protected $primaryKey = 'ajakem_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajakem_ajuan_id',
        'ajakem_jenis_id',
        'ajakem_nik',
        'ajakem_nama_jenazah',
        'ajakem_tgl_kematian',
        'ajakem_tempat_kematian',
        'ajakem_anak_ke',
        'ajakem_nama_ibu',
        'ajakem_nama_ayah',
        'ajakem_dokumen',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajakem_ajuan_id'     => 'integer',
        'ajakem_jenis_id'     => 'integer',
        'ajakem_anak_ke'      => 'integer',
        'ajakem_tgl_kematian' => 'datetime',
        'ajakem_dokumen'      => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'ajakem_ajuan_id', 'ajuan_id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajakem_jenis_id', 'ja_id');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function ajakemNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }
}
