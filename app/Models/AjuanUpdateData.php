<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanUpdateData
 *
 * @property int $ajud_id
 * @property int $ajud_ajuan_id
 * @property int $ajud_jenis_id
 * @property string|null $ajud_nik
 * @property string|null $ajud_nama_lengkap
 * @property array|null $ajud_dokumen
 *
 * @property-read Ajuan|null $ajuan
 * @property-read JenisAjuan|null $jenisAjuan
 */
final class AjuanUpdateData extends Model
{
    protected $table = 'ajuan_update_data';

    protected $primaryKey = 'ajud_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajud_ajuan_id',
        'ajud_jenis_id',
        'ajud_nik',
        'ajud_nama_lengkap',
        'ajud_dokumen',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajud_ajuan_id' => 'integer',
        'ajud_jenis_id' => 'integer',
        'ajud_dokumen'  => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'ajud_ajuan_id', 'ajuan_id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajud_jenis_id', 'ja_id');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function ajudNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }
}
