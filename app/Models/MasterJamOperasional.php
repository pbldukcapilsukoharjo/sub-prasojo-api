<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJamOperasional extends Model
{
    protected $table = 'master_jam_operasional';

    protected $fillable = [
        'hari_kode',
        'hari_nama',
        'jam_buka',
        'jam_tutup',
        'is_libur',
    ];

    protected $casts = [
        'hari_kode' => 'integer',
        'is_libur' => 'boolean',
    ];
}
