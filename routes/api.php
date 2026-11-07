<?php

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
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvatarController;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::get('/hello', [WelcomeController::class, 'hello']);
    Route::get('/greet/{name}', [WelcomeController::class, 'greet']);
    Route::get('/ping', function () {
        return response()->json([
            'pong' => true,
            'time' => now(),
        ]);
    });

    // Public authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/me/avatar', [AvatarController::class, 'store']);

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('quizzes', QuizController::class);
        Route::apiResource('questions', QuestionController::class);
        Route::apiResource('options', OptionController::class);
        Route::post(
            '/attempts/{attempt}/submit',
            [AttemptController::class, 'submit']
        );
        Route::apiResource('attempts', AttemptController::class)
            ->except(['update']);
        Route::patch(
            '/answers/{answer}/grade',
            [AnswerController::class, 'grade']
        );
        Route::apiResource('answers', AnswerController::class);
        Route::apiResource('leaderboards', LeaderboardController::class);
        Route::apiResource('users', UserController::class);
    });
});
