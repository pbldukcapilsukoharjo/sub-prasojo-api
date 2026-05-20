<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubUser extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'sub_users';

    protected $fillable = [
        'fullname',
        'email',
        'hashed_password',
    ];

    protected $hidden = [
        'hashed_password',
    ];
}
