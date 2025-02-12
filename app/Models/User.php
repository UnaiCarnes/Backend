<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Notifications\CustomVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'birth_date',
        'role',
        'deleted'
    ];
    
    /**
     *  The attributes that are most assignable
     * 
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

        /**
     *  The attributes that are most assignable
     * 
     * @return array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
    ];

    /**
     * Relación uno a uno: un usuario tiene un balance.
     */
    public function balance()
    {
        return $this->hasOne(Balance::class);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }
        // Añadir esta relación en el modelo User existente
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}