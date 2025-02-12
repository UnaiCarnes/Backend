<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GameStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email:rfc,dns', // Valida que sea un email real
                    'max:255',
                    'unique:users',
                    'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/i' // Solo permite emails de Gmail
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8', // Mínimo 8 caracteres
                    'confirmed', // Requiere password_confirmation
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/' // Debe contener al menos una mayúscula, una minúscula y un número
                ],
                'password_confirmation' => 'required',
                'birth_date' => [
                    'required',
                    'date',
                    'before_or_equal:' . now()->subYears(16)->format('Y-m-d'), // Mínimo 16 años
                    'after_or_equal:' . now()->subYears(100)->format('Y-m-d') // Máximo 100 años
                ]
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'email.required' => 'El correo es obligatorio.',
                'email.email' => 'El formato del correo no es válido.',
                'email.regex' => 'Solo se permiten correos de Gmail.',
                'email.unique' => 'Este correo ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
                'birth_date.before_or_equal' => 'Debes tener al menos 16 años para registrarte.',
                'birth_date.after_or_equal' => 'La fecha de nacimiento no es válida.'
            ]);

            // Revocar el token actual si existe
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }

            // Crear el usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'birth_date' => $request->birth_date
            ]);

            // Crear estadísticas iniciales
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

            // Enviar notificación de verificación de correo electrónico
            event(new Registered($user));

            // Crear token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['message' => 'Usuario registrado. Se ha enviado un correo de verificación.'], 201);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'birth_date' => $user->birth_date
                ],
                'token' => $token,
                'message' => 'Registration successful'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error during registration: ', $e->errors());
            return response()->json([
                'message' => 'Validation error',
                'error' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error during registration: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error during registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'El correo es obligatorio.',
                'email.email' => 'El formato del correo no es válido.',
                'password.required' => 'La contraseña es obligatoria.'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'El correo no está registrado.'], 404);
            }

            if (!$user->email_verified_at) {
                return response()->json(['message' => 'Por favor, verifica tu correo antes de iniciar sesión.'], 403);
            }

            if ($user->deleted) {
                return response()->json(['message' => 'Tu cuenta ha sido desactivada. Contacta con el administrador.'], 403);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Contraseña incorrecta.'], 401);
            }

            $user->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Login exitoso',
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en el login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // En tu AuthController
    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);
    
        if ($user->email_verified_at) {
            return response()->json(['message' => 'El correo ya ha sido verificado anteriormente.'], 400);
        }
    
        if (!hash_equals(sha1($user->email), $hash)) {
            return response()->json(['message' => 'El enlace de verificación no es válido.'], 403);
        }
    
        $user->email_verified_at = now();
        $user->save();
    
        return response()->json(['message' => 'Correo verificado con éxito. Ahora puedes iniciar sesión.']);
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

    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function checkEmail($email)
    {
        $exists = User::where('email', $email)->exists();
        return response()->json(['exists' => $exists]);
    }
}