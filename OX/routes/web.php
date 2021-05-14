<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('home');
})->middleware('auth');

Auth::routes();

// Statisztikák
Route::get('/stats', [App\Http\Controllers\StatsController::class, 'index'])->name('stats.index');

// Játék
Route::post('/games/{id}/step', [App\Http\Controllers\GamesController::class, 'step'])->name('games.step');
Route::get('/games/{id}/fetch', [App\Http\Controllers\GamesController::class, 'fetch'])->name('games.fetch');
Route::post('/games/{id}/join', [App\Http\Controllers\GamesController::class, 'join'])->name('games.join');
Route::resource('games', App\Http\Controllers\GamesController::class);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
