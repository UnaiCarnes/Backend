<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'getUserProfile']);
});

Route::get('/api/test', function () {
    return response()->json([
        'message' => 'Conexión exitosa con el backend',
        'status' => 'success'
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

Route::get('/api/profile', [ProfileController::class, 'getUserProfile']);