<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\JenisAjuan
 *
 * @property int $ja_id
 * @property string|null $ja_judul
 */
final class JenisAjuan extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'jenis_ajuan';

    protected $primaryKey = 'ja_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ja_judul',
    ];
}
