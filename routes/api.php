<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AjuanController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LembarKerjaController;
use App\Http\Controllers\Api\V1\ProdukController;
use App\Http\Controllers\Api\V1\ReviewController;

Route::prefix('v1')->group(function () {

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
    | Dashboard 
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
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
});