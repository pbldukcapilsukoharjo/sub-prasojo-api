<?php

use App\Http\Controllers\Api\V1\DistribusiWilayahController;
use App\Http\Controllers\Api\V1\UlasanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Ulasan
    |--------------------------------------------------------------------------
    */

    Route::prefix('ulasan')
        ->controller(UlasanController::class)
        ->group(function (): void {

            Route::get('/', 'index')
                ->name('api.v1.ulasan.index');

            Route::get('/kpi', 'kpi')
                ->name('api.v1.ulasan.kpi');
        });

    /*
    |--------------------------------------------------------------------------
    | Distribusi Wilayah
    |--------------------------------------------------------------------------
    */

    Route::prefix('distribusi-wilayah')
        ->controller(DistribusiWilayahController::class)
        ->group(function (): void {

            /**
             * GET /api/v1/distribusi-wilayah
             */
            Route::get('/', 'index')
                ->name('api.v1.distribusi-wilayah.index');
        });
});