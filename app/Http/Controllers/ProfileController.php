<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GameStatistic;
use Illuminate\Support\Facades\Log; // Asegúrate de importar Log

class ProfileController extends Controller
{
    public function getUserProfile(Request $request)
    {
        try {
            $user = $request->user(); // Obtiene el usuario autenticado
            
            // Verificar si el usuario está autenticado
            if (!$user) {
                Log::error('User not authenticated'); // Registro de error
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            Log::info('User ID: ' . $user->id); // Registro de depuración

            $statistics = GameStatistic::where('user_id', $user->id)->first();

            // Verificar si hay estadísticas para el usuario
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
            Log::error('Error fetching profile data: ' . $e->getMessage()); // Registro de error
            return response()->json([
                'message' => 'Error fetching profile data',
                'error' => $e->getMessage() // Esto te dará más información sobre el error
            ], 500);
        }
    }
}