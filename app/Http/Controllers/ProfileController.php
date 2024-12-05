<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GameStatistic;

class ProfileController extends Controller
{
    public function getUserProfile()
    {
        try {
            // Por ahora, para pruebas, obtén el primer usuario
            $user = User::first();
            $statistics = GameStatistic::where('user_id', $user->id)->first();

            if (!$statistics) {
                return response()->json([
                    'message' => 'No statistics found for this user'
                ], 404);
            }

            return response()->json([
                'userInfo' => [
                    'name' => $user->name,
                    'playerId' => $statistics->player_id,
                    'balance' => $statistics->balance,
                    'email' => $user->email,
                ],
                'gameStats' => [
                    'gamesPlayed' => $statistics->games_played,
                    'mostPlayedGame' => $statistics->most_played_game,
                    'gamesWon' => $statistics->games_won,
                    'gamesLost' => $statistics->games_lost,
                    'winRate' => $statistics->win_rate . '%',
                    'averageBet' => $statistics->average_bet,
                    'totalWinnings' => $statistics->total_winnings,
                    'totalLosses' => $statistics->total_losses,
                    'totalWL' => $statistics->total_winnings - $statistics->total_losses,
                ],
                'prizeHistory' => [
                    'lastPrize' => $statistics->last_prize,
                    'bestPrize' => $statistics->best_prize,
                    'highestBet' => $statistics->highest_bet,
                    'highestStreak' => $statistics->highest_streak,
                ],
                'consumables' => [
                    'alcoholicDrink' => $statistics->alcoholic_drink,
                    'hydratingDrink' => $statistics->hydrating_drink,
                    'toxicSubstances' => $statistics->toxic_substances,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}