<?php

use App\Http\Controllers\Api\V1\UlasanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->group(function (): void {

        Route::prefix('ulasan')
            ->controller(UlasanController::class)
            ->group(function (): void {

                /**
                 * GET /api/v1/ulasan
                 */
                Route::get(
                    '/',
                    'index'
                )->name('api.v1.ulasan.index');

                /**
                 * GET /api/v1/ulasan/kpi
                 */
                Route::get(
                    '/kpi',
                    'kpi'
                )->name('api.v1.ulasan.kpi');

                /**
                 * GET /api/v1/ulasan/export
                 * Belum diimplementasikan.
                 */
                // Route::get(
                //     '/export',
                //     'export'
                // )->name('api.v1.ulasan.export');
            });
    });