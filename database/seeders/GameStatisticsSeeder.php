<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GameStatistic;
use App\Models\User;

class GameStatisticsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            GameStatistic::create([
                'user_id' => $user->id,
                'player_id' => 'P' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
                'balance' => 1000.00,
                'games_played' => rand(0, 100),
                'most_played_game' => ['Poker', 'Blackjack', 'Roulette', 'Slots'][rand(0, 3)],
                'games_won' => rand(0, 50),
                'games_lost' => rand(0, 50),
                'win_rate' => rand(0, 100),
                'average_bet' => rand(10, 1000),
                'total_winnings' => rand(1000, 10000),
                'total_losses' => rand(500, 5000),
                'last_prize' => rand(100, 1000),
                'best_prize' => rand(1000, 5000),
                'highest_bet' => rand(500, 2000),
                'highest_streak' => rand(1, 10),
                'alcoholic_drink' => rand(0, 5),
                'hydrating_drink' => rand(0, 5),
                'toxic_substances' => rand(0, 5),
            ]);
        }
    }
}