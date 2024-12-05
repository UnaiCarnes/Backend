<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'player_id',
        'balance',
        'games_played',
        'most_played_game',
        'games_won',
        'games_lost',
        'win_rate',
        'average_bet',
        'total_winnings',
        'total_losses',
        'last_prize',
        'best_prize',
        'highest_bet',
        'highest_streak',
        'alcoholic_drink',
        'hydrating_drink',
        'toxic_substances',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'win_rate' => 'decimal:2',
        'average_bet' => 'decimal:2',
        'total_winnings' => 'decimal:2',
        'total_losses' => 'decimal:2',
        'last_prize' => 'decimal:2',
        'best_prize' => 'decimal:2',
        'highest_bet' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}