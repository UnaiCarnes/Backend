<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;

Route::get('/login', function () {
    return response()->json(['message' => 'Debes iniciar sesión'], 401);
})->name('login');
