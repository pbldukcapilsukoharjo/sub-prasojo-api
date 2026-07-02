<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanKia
 *
 * @property int $ajkia_id
 * @property int $ajkia_ajuan_id
 * @property int $ajkia_jenis_id
 * @property string|null $ajkia_nik
 * @property string|null $ajkia_nama_lengkap
 * @property array|null $ajkia_dokumen
 *
 * @property-read Ajuan|null $ajuan
 * @property-read JenisAjuan|null $jenisAjuan
 */
final class AjuanKia extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'ajuan_kia';

    protected $primaryKey = 'ajkia_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajkia_ajuan_id',
        'ajkia_jenis_id',
        'ajkia_nik',
        'ajkia_nama_lengkap',
        'ajkia_dokumen',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajkia_ajuan_id' => 'integer',
        'ajkia_jenis_id' => 'integer',
        'ajkia_dokumen'  => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'ajkia_ajuan_id', 'ajuan_id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajkia_jenis_id', 'ja_id');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function ajkiaNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }
}
