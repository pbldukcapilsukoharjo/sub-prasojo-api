<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Test
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
Route::post('/refresh', [\App\Http\Controllers\AuthController::class, 'refresh']);

Route::middleware('paseto.auth')->get('/me', function(Request $request) {
    return ['user_id' => $request->attributes->get('auth_user_id')];
});

Route::get('/mee', function (Request $request) {
    return ['user_id' => $request->attributes->get('auth_user_id')];
})->middleware('paseto.auth');