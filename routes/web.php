<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LeaderboardController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// Public Routes
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [GameController::class, 'index'])->name('game.index');
    Route::get('/game', [GameController::class, 'index'])->name('game.index');
});

// API Routes
Route::prefix('api')->middleware('auth')->group(function () {
    Route::post('/game/start', [GameController::class, 'startGame'])->name('api.game.start');
    Route::post('/game/save-result', [GameController::class, 'saveResult'])->name('api.game.save-result');
});
