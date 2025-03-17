<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

// start Game
Route::get('/', [MainController::class, 'startGame'])->name('start_game');
Route::post('/prepare_game', [MainController::class, 'prepareGame'])->name('prepare_game');

Route::get('game', [MainController::class, 'game'])->name('game');

Route::get('/answer/{answer}', [MainController::class, 'answer'])->name('answer');
Route::get('/next_question', [MainController::class, 'nextQuestion'])->name('next_question');

Route::get('/show_result', [MainController::class, 'showResult'])->name('show_result');