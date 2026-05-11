<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanKk
 *
 * @property int $ajkk_id
 * @property int $ajkk_ajuan_id
 * @property int $ajkk_jenis_id
 * @property string|null $ajkk_kk
 * @property string|null $ajkk_nama_kepala_keluarga
 * @property array|null $ajkk_dokumen
 *
 * @property-read Ajuan|null $ajuan
 * @property-read JenisAjuan|null $jenisAjuan
 */
final class AjuanKk extends Model
{
    protected $table = 'ajuan_kk';

    protected $primaryKey = 'ajkk_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajkk_ajuan_id',
        'ajkk_jenis_id',
        'ajkk_kk',
        'ajkk_nama_kepala_keluarga',
        'ajkk_dokumen',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajkk_ajuan_id' => 'integer',
        'ajkk_jenis_id' => 'integer',
        'ajkk_dokumen'  => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'ajkk_ajuan_id', 'ajuan_id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajkk_jenis_id', 'ja_id');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function ajkkKk(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }
}
