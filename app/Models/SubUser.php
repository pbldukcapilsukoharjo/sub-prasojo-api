<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Notifications\Notifiable;

class SubUser extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
    use HasFactory, SoftDeletes, HasUuids, Notifiable, CanResetPasswordTrait;

    protected $table = 'sub_users';

    protected $fillable = [
        'fullname',
        'email',
        'hashed_password',
        'verified_at'
    ];

    protected $hidden = [
        'hashed_password',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function getAuthPasswordName()
    {
        return 'hashed_password';
    }

    public function getAuthPassword()
    {
        return $this->hashed_password;
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->verified_at);
    }

    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function getEmailForVerification()
    {
        return $this->email;
    }
}
