<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatsController;

Route::get('/', function () {
    return redirect()->route('stats.index');
});

// Stats Routes
Route::get('/games', [StatsController::class, 'index'])->name('stats.index');
Route::get('/games/{id}', [StatsController::class, 'game'])->name('stats.game');
Route::get('/teams', [StatsController::class, 'teams'])->name('stats.teams');
Route::get('/teams/{id}', [StatsController::class, 'team'])->name('stats.team');
Route::get('/players', [StatsController::class, 'players'])->name('stats.players');
Route::get('/players/{id}', [StatsController::class, 'player'])->name('stats.player');
Route::get('/stats-vs-position', [StatsController::class, 'statsVsPosition'])->name('stats.vs-position');

