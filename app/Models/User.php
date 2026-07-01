<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password',
        'role', 'ulp_name', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function berkasArr()
    {
        return $this->hasMany(BerkasPrr::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUlp(): bool
    {
        return $this->role === 'ulp';
    }

    public function getUlpLabelAttribute(): string
    {
        return match($this->ulp_name) {
            'ulp_syiah_kuala' => 'ULP Syiah Kuala',
            'ulp_jantho'      => 'ULP Jantho',
            'ulp_sabang'      => 'ULP Sabang',
            'ulp_merduati'    => 'ULP Merduati',
            'ulp_lambaro'     => 'ULP Lambaro',
            'ulp_keudeu_bieng' => 'ULP Keudeu Bieng',
            default           => '-',
        };
    }
}
