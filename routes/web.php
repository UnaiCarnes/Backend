<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/api/test', function () {
    return response()->json([
        'message' => 'Conexión exitosa con el backend',
        'status' => 'success'
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

Route::get('/api/profile', [ProfileController::class, 'getUserProfile']);