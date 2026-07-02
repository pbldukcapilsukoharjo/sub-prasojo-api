<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AjuanKtpel
 *
 * @property int $ajktpel_id
 * @property int $ajktpel_ajuan_id
 * @property int $ajktpel_jenis_id
 * @property string|null $ajktpel_nik
 * @property string|null $ajktpel_nama_lengkap
 * @property string|null $ajktpel_gol_darah
 * @property array|null $ajktpel_dokumen
 *
 * @property-read Ajuan|null $ajuan
 * @property-read JenisAjuan|null $jenisAjuan
 */
final class AjuanKtpel extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'ajuan_ktpel';

    protected $primaryKey = 'ajktpel_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ajktpel_ajuan_id',
        'ajktpel_jenis_id',
        'ajktpel_nik',
        'ajktpel_nama_lengkap',
        'ajktpel_gol_darah',
        'ajktpel_dokumen',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ajktpel_ajuan_id' => 'integer',
        'ajktpel_jenis_id' => 'integer',
        'ajktpel_dokumen'  => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function ajuan(): BelongsTo
    {
        return $this->belongsTo(Ajuan::class, 'ajktpel_ajuan_id', 'ajuan_id');
    }

    public function jenisAjuan(): BelongsTo
    {
        return $this->belongsTo(JenisAjuan::class, 'ajktpel_jenis_id', 'ja_id');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    protected function ajktpelNik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value ? str_pad($value, 16, '0', STR_PAD_LEFT) : null,
            set: fn (?string $value): ?string => $value ? trim($value) : null,
        );
    }
}
