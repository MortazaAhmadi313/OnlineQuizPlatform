<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\UserController;

Route::get('/hello', [WelcomeController::class, 'hello']);
Route::get('/greet/{name}', [WelcomeController::class, 'greet']);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('quizzes', QuizController::class);
Route::apiResource('questions', QuestionController::class);
Route::apiResource('options', OptionController::class);
Route::apiResource('attempts', AttemptController::class);
Route::apiResource('answers', AnswerController::class);
Route::apiResource('leaderboards', LeaderboardController::class);
Route::apiResource('users', UserController::class);

Route::get('/ping', function () {
    return response()->json(['pong' => true, 'time' => now()]);
});
