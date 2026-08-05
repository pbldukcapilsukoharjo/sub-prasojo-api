<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanPindah
 *
 * @property int $ajp_id
 * @property int $ajp_ajuan_id
 * @property int $ajp_jenis_id
 * @property string|null $ajp_nik
 * @property string|null $ajp_kk
 * @property string|null $ajp_nama_lengkap
 * @property array|null $ajp_dokumen
 *
 * @property-read Ajuan|null $ajuan
 * @property-read JenisAjuan|null $jenisAjuan
 */
final class AjuanPindah extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'ajuan_pindah';

    protected $primaryKey = 'ajp_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajp_ajuan_id',
        'ajp_jenis_id',
        'ajp_nik',
        'ajp_kk',
        'ajp_nama_lengkap',
        'ajp_dokumen',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajp_ajuan_id' => 'integer',
        'ajp_jenis_id' => 'integer',
        'ajp_dokumen'  => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'ajp_ajuan_id', 'ajuan_id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajp_jenis_id', 'ja_id');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function ajpNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }

    protected function ajpKk(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }
}
