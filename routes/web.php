<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatsController;

Route::get('/', function () {
    return redirect()->route('games.index');
});

// Stats Routes
Route::get('/games', [StatsController::class, 'index'])->name('games.index');
Route::get('/games/{id}', [StatsController::class, 'game'])->name('games.show');
Route::get('/teams', [StatsController::class, 'teams'])->name('teams.index');
Route::get('/teams/{id}', [StatsController::class, 'team'])->name('teams.show');
Route::get('/players', [StatsController::class, 'players'])->name('players.index');
Route::get('/players/{id}', [StatsController::class, 'player'])->name('players.show');
Route::get('/stats-vs-position', [StatsController::class, 'statsVsPosition'])->name('stats.vs-position');
Route::get('/form-recommendations', [StatsController::class, 'formRecommendations'])->name('stats.form-recommendations');

// Sync Routes
Route::post('/sync-data', [StatsController::class, 'syncData'])->name('stats.sync');

