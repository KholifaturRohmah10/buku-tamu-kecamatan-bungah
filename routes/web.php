<?php

use App\Http\Controllers\AdminKunjunganTamuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KunjunganTamuController;
use App\Http\Controllers\LaporanSurveiController;
use App\Http\Controllers\ValidatorKunjunganTamuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->user() !== null) {
        return to_route($request->user()->dashboardRoute());
    }

    return view('welcome');
})->name('welcome');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn() => redirect('/?modal=internal'))->name('login');
    Route::post('/login', [AuthController::class, 'storeLogin'])->name('authenticate');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('tamu')
    ->name('kunjungan-tamu.')
    ->group(function (): void {
        Route::get('/', [KunjunganTamuController::class, 'index'])->name('index');
        Route::post('/kunjungan-tamu', [KunjunganTamuController::class, 'store'])->name('store');
        Route::get('/kunjungan-tamu/{kunjunganTamu}/survey', [KunjunganTamuController::class, 'createSurvey'])
            ->name('survey.create');
        Route::post('/kunjungan-tamu/{kunjunganTamu}/survey', [KunjunganTamuController::class, 'storeSurvey'])
            ->name('survey.store');
        Route::get('/kunjungan-tamu/{kunjunganTamu}/success', [KunjunganTamuController::class, 'success'])
            ->name('success');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function (): void {
        Route::get('/', [AdminKunjunganTamuController::class, 'index'])->name('dashboard');
        Route::get('/kunjungan-tamu/{kunjunganTamu}/edit', [AdminKunjunganTamuController::class, 'edit'])
            ->name('kunjungan-tamu.edit');
        Route::put('/kunjungan-tamu/{kunjunganTamu}', [AdminKunjunganTamuController::class, 'update'])
            ->name('kunjungan-tamu.update');
        Route::delete('/kunjungan-tamu/{kunjunganTamu}', [AdminKunjunganTamuController::class, 'destroy'])
            ->name('kunjungan-tamu.destroy');
        Route::get('/kunjungan-tamu/print', [AdminKunjunganTamuController::class, 'print'])
            ->name('kunjungan-tamu.print');
        Route::get('/kunjungan-tamu/{kunjunganTamu}/receipt', [AdminKunjunganTamuController::class, 'printReceipt'])
            ->name('kunjungan-tamu.receipt');
        Route::get('/kunjungan-tamu/export/{format}', [AdminKunjunganTamuController::class, 'export'])
            ->name('kunjungan-tamu.export');
        Route::get('/survey-reports', [LaporanSurveiController::class, 'index'])
            ->defaults('panel', 'admin')
            ->name('survey-reports.index');
        Route::get('/survey-reports/print', [LaporanSurveiController::class, 'print'])
            ->defaults('panel', 'admin')
            ->name('survey-reports.print');
        Route::get('/survey-reports/export/{format}', [LaporanSurveiController::class, 'export'])
            ->defaults('panel', 'admin')
            ->name('survey-reports.export');
        Route::get('/survey-reports/{survey}', [LaporanSurveiController::class, 'show'])
            ->defaults('panel', 'admin')
            ->name('survey-reports.show');
        Route::get('/survey-reports/{survey}/print', [LaporanSurveiController::class, 'printSingle'])
            ->defaults('panel', 'admin')
            ->name('survey-reports.print-single');
    });

Route::prefix('validator')
    ->name('validator.')
    ->middleware(['auth', 'role:validator'])
    ->group(function (): void {
        Route::get('/', [ValidatorKunjunganTamuController::class, 'index'])->name('dashboard');
        Route::patch('/kunjungan-tamu/{kunjunganTamu}/status', [ValidatorKunjunganTamuController::class, 'update'])
            ->name('kunjungan-tamu.status');
        Route::get('/kunjungan-tamu/print', [ValidatorKunjunganTamuController::class, 'print'])
            ->name('kunjungan-tamu.print');
        Route::get('/kunjungan-tamu/{kunjunganTamu}/receipt', [ValidatorKunjunganTamuController::class, 'printReceipt'])
            ->name('kunjungan-tamu.receipt');
        Route::get('/kunjungan-tamu/export/{format}', [ValidatorKunjunganTamuController::class, 'export'])
            ->name('kunjungan-tamu.export');
    });
