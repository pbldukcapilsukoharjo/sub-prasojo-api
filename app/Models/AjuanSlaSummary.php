<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AjuanSlaSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'ajuan_id',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_sla_menit',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_sla_menit' => 'integer',
    ];
}
