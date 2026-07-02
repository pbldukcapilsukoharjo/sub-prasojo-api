<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanDatang
 *
 * @property int $ajd_id
 * @property int $ajd_ajuan_id
 * @property int $ajd_jenis_id
 * @property string|null $ajd_nik
 * @property string|null $ajd_no_pindah
 * @property string|null $ajd_nama_lengkap
 * @property array|null $ajd_dokumen
 *
 * @property-read Ajuan|null $ajuan
 * @property-read JenisAjuan|null $jenisAjuan
 */
final class AjuanDatang extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'ajuan_datang';

    protected $primaryKey = 'ajd_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajd_ajuan_id',
        'ajd_jenis_id',
        'ajd_nik',
        'ajd_no_pindah',
        'ajd_nama_lengkap',
        'ajd_dokumen',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajd_ajuan_id' => 'integer',
        'ajd_jenis_id' => 'integer',
        'ajd_dokumen'  => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'ajd_ajuan_id', 'ajuan_id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajd_jenis_id', 'ja_id');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function ajdNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }
}
