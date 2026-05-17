<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\LembarKerjaController;

Route::prefix('v1')->group(function () {

    Route::prefix('lembar-kerja')->group(function () {

        // GET /api/v1/lembar-kerja
        Route::get('/', [LembarKerjaController::class, 'index']);

        // GET /api/v1/lembar-kerja/{lk_id}
        Route::get('/{lk_id}', [LembarKerjaController::class, 'show']);

    });

});