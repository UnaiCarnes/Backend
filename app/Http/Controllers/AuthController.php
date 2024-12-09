<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GameStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'birth_date' => 'required|date'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'birth_date' => $request->birth_date
        ]);

        // Crear estadísticas iniciales para el usuario
        GameStatistic::create([
            'user_id' => $user->id,
            'player_id' => 'P' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
            'balance' => 1000.00,
            'games_played' => 0,
            'most_played_game' => 'None',
            'games_won' => 0,
            'games_lost' => 0,
            'win_rate' => 0,
            'average_bet' => 0,
            'total_winnings' => 0,
            'total_losses' => 0,
            'last_prize' => 0,
            'best_prize' => 0,
            'highest_bet' => 0,
            'highest_streak' => 0,
            'alcoholic_drink' => 0,
            'hydrating_drink' => 0,
            'toxic_substances' => 0,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Registration successful'
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $statistics = GameStatistic::where('user_id', $user->id)->first();

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
                'winRate' => $statistics->win_rate,
                'averageBet' => $statistics->average_bet,
                'totalWinnings' => $statistics->total_winnings,
                'totalLosses' => $statistics->total_losses,
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
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $request->user()->id,
            'birth_date' => 'date'
        ]);

        $request->user()->update($request->only(['name', 'email', 'birth_date']));

        return response()->json(['message' => 'Profile updated successfully']);
    }
}