<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\BppTempatPemakamanJenis
 *
 * @property int $bppj_id
 * @property string|null $bppj_title
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BppTempatPemakaman> $tempatPemakamans
 */
final class BppTempatPemakamanJenis extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'bpp_tempat_pemakaman_jenis';

    protected $primaryKey = 'bppj_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'bppj_title',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function tempatPemakamans(): HasMany
    {
        return $this->hasMany(BppTempatPemakaman::class, 'bpptp_jenis', 'bppj_title');
    }
}
