<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $table = 'refresh_tokens';

    protected $fillable = [
        'sub_user_id',
        'jti',
        'expires_at',
        'revoked',
    ];

    protected $hidden = [
        'sub_user_id',
        'jti',
    ];
}
