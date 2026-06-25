<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\SLAController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LembarKerjaController;
use App\Http\Controllers\Api\V1\AjuanController;
use App\Http\Controllers\Api\V1\ProdukController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\DistribusiWilayahController;
use App\Http\Controllers\Api\V1\UlasanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('/register', 'register'); 
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
    });

    Route::middleware('paseto.auth')->group(function () {
        Route::get('/me', [UserController::class, 'getUser']);
    });

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    );

    /*
    |--------------------------------------------------------------------------
    | Lembar Kerja
    |--------------------------------------------------------------------------
    */
    Route::prefix('lembar-kerja')->group(function () {

        Route::get(
            '/',
            [LembarKerjaController::class, 'index']
        );

        Route::get(
            '/{lk_id}',
            [LembarKerjaController::class, 'show']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Ajuan
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/ajuan',
        [AjuanController::class, 'index']
    );

    Route::get(
        '/ajuan/{ajuan_id}',
        [AjuanController::class, 'show']
    );

    /*
    |--------------------------------------------------------------------------
    | Produk
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/produk',
        [ProdukController::class, 'index']
    );

    Route::get(
        '/produk/{produk_id}',
        [ProdukController::class, 'show']
    );

    /*
    |--------------------------------------------------------------------------
    | Review / Ulasan
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/review',
        [ReviewController::class, 'index']
    );

    Route::get(
        '/review/{review_id}',
        [ReviewController::class, 'show']
    );

    /*
    |--------------------------------------------------------------------------
    | Ulasan (Tambahan)
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/ulasan',
        [UlasanController::class, 'index']
    );

    /*
    |--------------------------------------------------------------------------
    | Distribusi Wilayah
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/distribusi-wilayah',
        [DistribusiWilayahController::class, 'index']
    );
    
    /*
    |--------------------------------------------------------------------------
    | SLA
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/sla',
        [SLAController::class, 'index']
    );
});