<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;

// Test de conexión
Route::get('/test', function () {
    return response()->json([
        'message' => 'Conexión exitosa con la API',
        'status' => 'OK'
    ]);
});

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Ruta de verificación del correo electrónico
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])->name('verification.verify');

// Rutas protegidas (requieren autenticación y verificación)
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'getUserProfile']);
    Route::put('/profile', [ProfileController::class, 'updateUserProfile']);
    Route::put('/profile/balance', [ProfileController::class, 'updateBalance']);
    Route::put('/profile/statistics', [ProfileController::class, 'updateGameStatistics']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/loans/options', [LoanController::class, 'getLoanOptions']);
    Route::get('/loans/active', [LoanController::class, 'getActiveLoans']);
    Route::post('/loans/take', [LoanController::class, 'takeLoan']);
});

// Rutas para administradores
Route::middleware(['auth:sanctum', \App\Http\MiddleWare\AdminMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::post('/admin/manage-users', [AdminController::class, 'manageUsers']);
    Route::post('/admin/manage-games', [AdminController::class, 'manageGames']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::put('/loans/edit', [LoanController::class, 'editLoan']);

    // Nueva ruta para ocultar o activar préstamo
    Route::put('/loans/hide', [LoanController::class, 'hideLoan']);
});


// Ruta protegida con middleware IsAdmin
Route::get('/api/users', [UserController::class, 'getUsers'])->middleware(\App\Http\Middleware\IsAdmin::class);

