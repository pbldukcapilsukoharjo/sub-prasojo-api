<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\FilterController;
use App\Http\Controllers\Api\V1\OperatorController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\PengajuanController;
use App\Http\Controllers\Api\V1\ProdukController;
use App\Http\Controllers\Api\V1\UlasanController;
use App\Http\Controllers\Api\V1\HolidayController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WilayahController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->middleware('throttle:10,1')->controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');

        Route::post('/forgot-password', 'forgotPassword')->middleware('throttle:3,1')->name('password.email');
        Route::post('/reset-password', 'resetPassword')->name('password.update');
    });

    Route::prefix('email')->controller(AuthController::class)->group(function () {
        Route::get('/verify', 'verificationNotice')->name('verification.notice');
        Route::get('/verify/{id}/{hash}', 'verifyEmail')->middleware(['signed'])->name('verification.verify');
        Route::post('/resend', 'resendVerification')->middleware(['throttle:6,1'])->name('verification.send');
    });

    Route::middleware('paseto.auth')->prefix('auth')->group(function () {
        Route::get('/me', [UserController::class, 'getUser'])->middleware('verified');
        Route::put('/profile', [UserController::class, 'updateProfile'])->middleware('verified');
    });

    /*
    |--------------------------------------------------------------------------
    | Peringkat Operator
    |--------------------------------------------------------------------------
    */
    Route::prefix('operator')
        ->middleware('paseto.auth')
        ->controller(OperatorController::class)
        ->group(function (): void {
            Route::get('/kpi-global', 'kpiGlobal')->name('api.v1.operator.kpi-global');
            Route::get('/export', 'export')->name('api.v1.operator.export');
            Route::get('/peringkat', 'peringkat')->name('api.v1.operator.peringkat');
            Route::get('/{id}/kpi', 'kpi')->name('api.v1.operator.kpi');
            Route::get('/{id}/riwayat', 'riwayat')->name('api.v1.operator.riwayat');
        });
        
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->middleware('paseto.auth')->group(function () {
        Route::get('/kpi', [DashboardController::class, 'kpi']);
        Route::get('/chart-trend', [DashboardController::class, 'chartTrend']);
        Route::get('/top-wilayah', [DashboardController::class, 'topWilayah']);
    });

    /*
    |--------------------------------------------------------------------------
    | Pengajuan (Lembar Kerja, Ajuan, Produk)
    |--------------------------------------------------------------------------
    */
    Route::prefix('pengajuan')->middleware('paseto.auth')->group(function () {
        Route::controller(PengajuanController::class)->group(function () {
            Route::get('/export', 'export')->name('api.v1.pengajuan.export');
            Route::get('/lembar-kerja', 'getLembarKerja');
            Route::get('/ajuan', 'getAjuan');
            Route::get('/ajuan/chart', 'getAjuanChart');
            Route::get('/produk', 'getProduk');
            Route::get('/{ajuan_id}/detail', 'getDetailTimeline');
        });

        Route::get('/produk/{produk_id}/detail', [ProdukController::class, 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Ulasan (Tambahan)
    |--------------------------------------------------------------------------
    */
    Route::prefix('ulasan')->middleware('paseto.auth')->controller(UlasanController::class)->group(function () {
        Route::get('/kpi', 'kpi');
        Route::get('/', 'index');
        Route::get('/export', 'export');
    });

    /*
    |--------------------------------------------------------------------------
    | Monitoring Wilayah
    |--------------------------------------------------------------------------
    */
    Route::prefix('wilayah')->middleware('paseto.auth')->controller(\App\Http\Controllers\Api\V1\WilayahController::class)->group(function () {
        Route::get('/distribusi', 'distribusi');
        Route::get('/kpi', 'kpi');
        Route::get('/export', 'export');
    });

    /*
    |--------------------------------------------------------------------------
    | SLA
    |--------------------------------------------------------------------------
    */
    Route::prefix('sla')->middleware('paseto.auth')->controller(\App\Http\Controllers\Api\V1\SLAController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/kpi', 'kpi');
        Route::get('/samples', 'samples');
        Route::get('/export', 'export');
        Route::post('/recalculate', 'recalculate');
        Route::get('/target', 'getSlaTarget');
        Route::put('/target', 'updateSlaTarget');
        Route::patch('/target', 'updateSlaTarget');
    });

    /*
    |--------------------------------------------------------------------------
    | Jam Operasional
    |--------------------------------------------------------------------------
    */
    Route::prefix('operational-hours')->middleware('paseto.auth')->controller(\App\Http\Controllers\Api\V1\OperationalHourController::class)->group(function () {
        Route::get('/', 'index');
        Route::put('/{id}', 'update');
        Route::patch('/{id}', 'update');
    });

    /*
    |--------------------------------------------------------------------------
    | Master Hari Libur Nasional
    |--------------------------------------------------------------------------
    */
    Route::prefix('holidays')->middleware('paseto.auth')
        ->controller(HolidayController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/template', 'template');
            Route::post('/import', 'import');
            Route::delete('/bulk', 'destroyBulk');
            Route::put('/{id}', 'update');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Filter Dropdown
    |--------------------------------------------------------------------------
    */
    Route::prefix('filter')->middleware('paseto.auth')->controller(FilterController::class)->group(function () {
        Route::get('/layanan', 'layanan');
        Route::get('/kecamatan', 'kecamatan');
        Route::get('/pelapor', 'pelapor');
        Route::get('/status', 'status');
        Route::get('/jenis-ajuan', 'jenisAjuan');
        Route::get('/jalur', 'jalur');
    });
});
