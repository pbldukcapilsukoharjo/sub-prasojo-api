<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\PeringkatOperatorController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LembarKerjaController;
use App\Http\Controllers\Api\V1\AjuanController;
use App\Http\Controllers\Api\V1\ProdukController;
use App\Http\Controllers\Api\V1\UlasanController;
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
    Route::prefix('peringkat-operator')
        ->controller(PeringkatOperatorController::class)
        ->group(function (): void {
            Route::get('/', 'index')->name('api.v1.peringkat-operator.index');
            Route::get('/{operator_id}', 'show')->name('api.v1.peringkat-operator.show');
        });

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->group(function () {
        Route::get('/kpi', [DashboardController::class, 'kpi']);
        Route::get('/chart-trend', [DashboardController::class, 'chartTrend']);
        Route::get('/top-wilayah', [DashboardController::class, 'topWilayah']);
    });

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
    | Pengajuan (Master Table)
    |--------------------------------------------------------------------------
    */
    Route::prefix('pengajuan')->controller(AjuanController::class)->group(function () {
        Route::get('/', 'masterIndex');
        Route::get('/export', 'masterExport');
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
    | Ulasan (Tambahan)
    |--------------------------------------------------------------------------
    */
    Route::prefix('ulasan')->controller(UlasanController::class)->group(function () {
        Route::get('/kpi', 'kpi');
        Route::get('/', 'index');
        Route::get('/export', 'export');
    });

    /*
    |--------------------------------------------------------------------------
    | Monitoring Wilayah
    |--------------------------------------------------------------------------
    */
    Route::prefix('wilayah')->controller(WilayahController::class)->group(function () {
        Route::get('/distribusi', 'distribusi');
        Route::get('/export', 'export');
    });
    
    /*
    |--------------------------------------------------------------------------
    | SLA
    |--------------------------------------------------------------------------
    */
    Route::prefix('sla')->controller(\App\Http\Controllers\SlaController::class)->group(function () {
        Route::get('/kpi', 'kpi');
        Route::get('/layanan', 'layanan');
        Route::get('/export', 'export');
    });
});