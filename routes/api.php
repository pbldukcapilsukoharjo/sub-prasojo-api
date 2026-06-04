<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\LembarKerjaController;
use App\Http\Controllers\Api\V1\AjuanController;
use App\Http\Controllers\Api\V1\ProdukController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ReviewController;

Route::prefix('v1')->group(function () {

    Route::prefix('lembar-kerja')->group(function () {

        // GET /api/v1/lembar-kerja
        Route::get('/', [LembarKerjaController::class, 'index']);

        // GET /api/v1/lembar-kerja/{lk_id}
        Route::get('/{lk_id}', [LembarKerjaController::class, 'show']);

    });

    Route::get(
        '/ajuan',
        [AjuanController::class, 'index']
    );

    Route::get(
        '/ajuan/{ajuan_id}',
        [AjuanController::class, 'show']
    );

    Route::get(
        '/produk',
        [ProdukController::class, 'index']
    );

    Route::get(
        '/produk/{produk_id}',
        [ProdukController::class, 'show']
    );

    Route::prefix('dashboard')->group(function () {

        Route::get(
            '/',
            [DashboardController::class, 'index']
        );

        Route::get(
            '/distribusi-wilayah',
            [DashboardController::class, 'distribusiWilayah']
        );

        Route::get(
            '/peringkat-operator',
            [DashboardController::class, 'peringkatOperator']
        );

    });

    // Route untuk ulasan (bahasa Indonesia) - API endpoint terpisah
    Route::prefix('ulasan')->group(function () {

        Route::get(
            '/',
            [ReviewController::class, 'index']
        );

        Route::get(
            '/{review_id}',
            [ReviewController::class, 'show']
        );

        Route::post(
            '/',
            [ReviewController::class, 'store']
        );
    });

});