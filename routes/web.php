<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Añade esta ruta de prueba
Route::get('/test', function () {
    return response()->json([
        'message' => 'Conexión exitosa con el backend',
        'status' => 'success'
    ]);
});

// Tus rutas existentes se mantienen
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout']);

