<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('/register', 'register'); 
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');

        Route::prefix('email')->group(function () {
            Route::get('/verify', 'verificationNotice')->name('verification.notice');
            Route::get('/verify/{id}/{hash}', 'verifyEmail')->name('verification.verify');
            Route::post('/resend', 'resendVerification')->name('verification.send');
        });
    });

    Route::middleware(['paseto.auth', 'verified'])->group(function () {
        Route::get('/me', [UserController::class, 'getUser']);
    });
});