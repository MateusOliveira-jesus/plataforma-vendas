<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;


Route::get('/', [ApiController::class, 'index']);
Route::get('/users', [UserController::class, 'getUsers']);

// Rotas públicas
Route::prefix('auth')->group(function () {
    Route::middleware('check.method:POST')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });
});
Route::get('/login', function () {
    return response()->json([
        'message' => 'Esta é uma API. Use /api/auth/login para autenticar.'
    ], 404);
})->name('login');
// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/upload-avatar', [AuthController::class, 'uploadAvatar']);
    Route::get('/tokens', [AuthController::class, 'listTokens']);
    Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken']);
    Route::get('/check-token', [AuthController::class, 'checkToken']);

    //** */
});
