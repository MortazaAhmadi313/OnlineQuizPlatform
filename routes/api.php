<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;

Route::get('/hello', [WelcomeController::class, 'hello']);
Route::get('/greet/{name}', [WelcomeController::class, 'greet']);

Route::get('/ping', function () {
    return response()->json(['pong' => true, 'time' => now()]);
});
