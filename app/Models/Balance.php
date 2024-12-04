<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'balance'];

    // Relación inversa: el balance pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
