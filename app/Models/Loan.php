<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'amount',
        'interest_rate',
        'total_bets',
        'remaining_bets',
        'total_to_pay',
        'is_active', 
        'hidden'

    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_to_pay' => 'decimal:2',
        'hidden' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}   