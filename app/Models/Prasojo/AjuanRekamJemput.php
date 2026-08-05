<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanRekamJemput
 *
 * @property int $ajrj_id
 * @property int $ajrj_ajuan_id
 * @property int $ajrj_jenis_id
 * @property string|null $ajrj_nik
 * @property string|null $ajrj_nama_lengkap
 * @property string|null $ajrj_alasan
 * @property array|null $ajrj_dokumen
 *
 * @property-read Ajuan|null $ajuan
 * @property-read JenisAjuan|null $jenisAjuan
 */
final class AjuanRekamJemput extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'ajuan_rekam_jemput';

    protected $primaryKey = 'ajrj_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajrj_ajuan_id',
        'ajrj_jenis_id',
        'ajrj_nik',
        'ajrj_nama_lengkap',
        'ajrj_alasan',
        'ajrj_dokumen',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajrj_ajuan_id' => 'integer',
        'ajrj_jenis_id' => 'integer',
        'ajrj_dokumen'  => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'ajrj_ajuan_id', 'ajuan_id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajrj_jenis_id', 'ja_id');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function ajrjNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }
}
