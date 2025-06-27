<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::get('/results', [QuizController::class, 'showResults'])->name('quiz.results');

Route::prefix('api/quiz')->name('api.quiz.')->group(function () {
    Route::post('/start', [QuizController::class, 'startQuiz'])->name('start');
    Route::get('/question', [QuizController::class, 'getQuestion'])->name('question');
    Route::post('/answer', [QuizController::class, 'submitAnswer'])->name('answer');
    Route::get('/results', [QuizController::class, 'getResults'])->name('results');
    Route::post('/reset', [QuizController::class, 'resetQuiz'])->name('reset');
});
