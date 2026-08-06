<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Monitoring\SubUser;

class UserAjuanSlaSummary extends Model
{
    use HasFactory;

    protected $table = 'user_ajuan_sla_summaries';

    protected $fillable = [
        'user_id',
        'ajuan_id',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_sla_menit',
        'target_waktu_selesai',
        'operator_user_id',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_sla_menit' => 'integer',
        'target_waktu_selesai' => 'datetime',
        'operator_user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(SubUser::class, 'user_id', 'id');
    }
}
