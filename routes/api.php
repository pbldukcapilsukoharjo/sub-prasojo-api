<?php

use App\Http\Controllers\Api\V1\DistribusiWilayahController;
use App\Http\Controllers\Api\V1\PeringkatOperatorController;
use App\Http\Controllers\Api\V1\SlaController;
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
            Route::get('/', 'index')->name('api.v1.ulasan.index');
            Route::get('/kpi', 'kpi')->name('api.v1.ulasan.kpi');
        });

    /*
    |--------------------------------------------------------------------------
    | Distribusi Wilayah
    |--------------------------------------------------------------------------
    */

    Route::prefix('distribusi-wilayah')
        ->controller(DistribusiWilayahController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('api.v1.distribusi-wilayah.index');
        });

    /*
    |--------------------------------------------------------------------------
    | Peringkat Operator
    |--------------------------------------------------------------------------
    */

    Route::prefix('peringkat-operator')
        ->controller(PeringkatOperatorController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('api.v1.peringkat-operator.index');
            Route::get('/{operator_id}', 'show')->name('api.v1.peringkat-operator.show');
        });


    /*
    |--------------------------------------------------------------------------
    | SLA
    |--------------------------------------------------------------------------
    */

    Route::prefix('sla')
        ->controller(SlaController::class)
        ->group(function (): void {

            /*
            |----------------------------------------------------------------------
            | List SLA
            | GET /api/v1/sla
            |----------------------------------------------------------------------
            */

            Route::get('/', 'index')
                ->name('api.v1.sla.index');

            /*
            |----------------------------------------------------------------------
            | KPI SLA
            | GET /api/v1/sla/kpi
            |----------------------------------------------------------------------
            */

            Route::get('/kpi', 'kpi')
                ->name('api.v1.sla.kpi');
    });
      

});