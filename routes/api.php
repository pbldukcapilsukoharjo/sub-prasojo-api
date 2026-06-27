<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\DistribusiWilayahController;
use App\Http\Controllers\Api\V1\PeringkatOperatorController;
use App\Http\Controllers\Api\V1\SLAController;
use App\Http\Controllers\Api\V1\UlasanController;

Route::prefix('v1')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Ulasan
    |--------------------------------------------------------------------------
    */
    Route::prefix('ulasan')->group(function (): void {

        Route::get(
            '/',
            [UlasanController::class, 'index']
        );

        Route::get(
            '/{ulasan_id}',
            [UlasanController::class, 'show']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | SLA
    |--------------------------------------------------------------------------
    */
    Route::prefix('sla')->group(function (): void {

        Route::get(
            '/',
            [SLAController::class, 'index']
        );

        Route::get(
            '/{sla_id}',
            [SLAController::class, 'show']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Distribusi Wilayah
    |--------------------------------------------------------------------------
    */
    Route::prefix('distribusi-wilayah')->group(function (): void {

        Route::get(
            '/',
            [DistribusiWilayahController::class, 'index']
        );

        Route::get(
            '/{wilayah_id}',
            [DistribusiWilayahController::class, 'show']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Peringkat Operator
    |--------------------------------------------------------------------------
    */
    Route::prefix('peringkat-operator')->group(function (): void {

        Route::get(
            '/',
            [PeringkatOperatorController::class, 'index']
        );

        Route::get(
            '/{operator_id}',
            [PeringkatOperatorController::class, 'show']
        );
    });

});