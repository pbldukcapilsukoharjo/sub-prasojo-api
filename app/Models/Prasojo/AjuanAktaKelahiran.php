<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanAktaKelahiran
 *
 * @property int $ajakel_id
 * @property int $ajakel_ajuan_id
 * @property int $ajakel_jenis_id
 * @property string|null $ajakel_nik
 * @property string|null $ajakel_nama_bayi
 * @property string|null $ajakel_jenis_kelamin
 * @property string|null $ajakel_tempat_lahir
 * @property \Illuminate\Support\Carbon|null $ajakel_tgl_lahir
 * @property \Illuminate\Support\Carbon|null $ajakel_tgl_kawin
 * @property int $ajakel_anak_ke
 * @property string|null $ajakel_nama_ibu
 * @property string|null $ajakel_nama_ayah
 * @property array|null $ajakel_dokumen
 *
 * @property-read Ajuan|null $ajuan
 * @property-read JenisAjuan|null $jenisAjuan
 */
final class AjuanAktaKelahiran extends Model
{
    protected $connection = 'mysql_prasojo';

    public const string JENIS_KELAMIN_LAKI = 'LAKI-LAKI';
    public const string JENIS_KELAMIN_PEREMPUAN = 'PEREMPUAN';

    protected $table = 'ajuan_akta_kelahiran';

    protected $primaryKey = 'ajakel_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajakel_ajuan_id',
        'ajakel_jenis_id',
        'ajakel_nik',
        'ajakel_nama_bayi',
        'ajakel_jenis_kelamin',
        'ajakel_tempat_lahir',
        'ajakel_tgl_lahir',
        'ajakel_tgl_kawin',
        'ajakel_anak_ke',
        'ajakel_nama_ibu',
        'ajakel_nama_ayah',
        'ajakel_dokumen',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajakel_ajuan_id' => 'integer',
        'ajakel_jenis_id' => 'integer',
        'ajakel_anak_ke'  => 'integer',
        'ajakel_tgl_lahir' => 'date',
        'ajakel_tgl_kawin' => 'date',
        'ajakel_dokumen'   => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'ajakel_ajuan_id', 'ajuan_id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajakel_jenis_id', 'ja_id');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function ajakelNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }
}
