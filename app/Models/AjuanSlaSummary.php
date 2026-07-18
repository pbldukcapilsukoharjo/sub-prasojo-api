<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AjuanSlaSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'ajuan_id',
        'operator_user_id',
        'target_sla_menit_aktual',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_sla_menit',
        'durasi_kondisi_a_menit',
        'durasi_kondisi_b_menit',
        'target_waktu_selesai_kondisi_a',
        'target_waktu_selesai_kondisi_b',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_sla_menit' => 'integer',
        'target_sla_menit_aktual' => 'integer',
        'durasi_kondisi_a_menit' => 'integer',
        'durasi_kondisi_b_menit' => 'integer',
        'target_waktu_selesai_kondisi_a' => 'datetime',
        'target_waktu_selesai_kondisi_b' => 'datetime',
    ];
}
