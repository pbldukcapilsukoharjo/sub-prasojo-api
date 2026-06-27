<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\UlasanController;
use App\Http\Controllers\Api\V1\SLAController;
use App\Http\Controllers\Api\V1\DistribusiWilayahController;
use App\Http\Controllers\Api\V1\PeringkatOperatorController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Ulasan
    |--------------------------------------------------------------------------
    */

    Route::prefix('ulasan')->group(function () {

        Route::get(
            '/',
            [UlasanController::class, 'index']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | SLA
    |--------------------------------------------------------------------------
    */

    Route::prefix('sla')->group(function () {

        Route::get(
            '/',
            [SLAController::class, 'index']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Distribusi Wilayah
    |--------------------------------------------------------------------------
    */

    Route::prefix('distribusi-wilayah')->group(function () {

        Route::get(
            '/',
            [DistribusiWilayahController::class, 'index']
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Peringkat Operator
    |--------------------------------------------------------------------------
    */

    Route::prefix('peringkat-operator')->group(function () {

        Route::get(
            '/',
            [PeringkatOperatorController::class, 'index']
        );
    });

});