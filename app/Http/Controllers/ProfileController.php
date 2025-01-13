<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GameStatistic;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function getUserProfile(Request $request)
    {
        try {
            $user = $request->user(); // Obtiene el usuario autenticado
            
            // Verificar si el usuario está autenticado
            if (!$user) {
                Log::error('User not authenticated');
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            Log::info('User ID: ' . $user->id); // Registro para depuración

            // Intentar obtener estadísticas de juego si existen
            $statistics = GameStatistic::where('user_id', $user->id)->first();

            // Datos básicos del perfil
            $profileData = [
                'userInfo' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role, // Asegurarse de incluir el rol del usuario
                ],
            ];

            // Si el usuario tiene estadísticas de juego, añadirlas al perfil
            if ($statistics) {
                $profileData['userInfo']['playerId'] = $statistics->player_id;
                $profileData['userInfo']['balance'] = $statistics->balance;

                $profileData['gameStats'] = [
                    'gamesPlayed' => $statistics->games_played,
                    'mostPlayedGame' => $statistics->most_played_game,
                    'gamesWon' => $statistics->games_won,
                    'gamesLost' => $statistics->games_lost,
                    'winRate' => $statistics->win_rate . '%',
                    'averageBet' => $statistics->average_bet,
                    'totalWinnings' => $statistics->total_winnings,
                    'totalLosses' => $statistics->total_losses,
                    'totalWL' => $statistics->total_winnings - $statistics->total_losses,
                ];

                $profileData['prizeHistory'] = [
                    'lastPrize' => $statistics->last_prize,
                    'bestPrize' => $statistics->best_prize,
                    'highestBet' => $statistics->highest_bet,
                    'highestStreak' => $statistics->highest_streak,
                ];

                $profileData['consumables'] = [
                    'alcoholicDrink' => $statistics->alcoholic_drink,
                    'hydratingDrink' => $statistics->hydrating_drink,
                    'toxicSubstances' => $statistics->toxic_substances,
                ];
            }

            return response()->json($profileData);
        } catch (\Exception $e) {
            Log::error('Error fetching profile data: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching profile data',
                'error' => $e->getMessage() // Esto te dará más información sobre el error
            ], 500);
        }

        return response()->json([
            'user' => $request->user()
        ]);
    }
    public function updateUserProfile(Request $request)
    {
        try {
            $user = $request->user(); // Obtiene el usuario autenticado
            
            // Verificar si el usuario está autenticado
            if (!$user) {
                Log::error('User not authenticated');
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            // Validar la entrada
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Actualizar el nombre del usuario
            $user->name = $request->name;
            $user->save();

            return response()->json(['message' => 'Profile updated successfully']);
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating profile',
                'error' => $e->getMessage() // Esto te dará más información sobre el error
            ], 500);
        }
    }
}
