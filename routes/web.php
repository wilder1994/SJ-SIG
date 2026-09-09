<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NoveltyController;
use App\Http\Controllers\ParafiscalController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ServiceDeliveryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function (): void {
    Route::get('/ingreso', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/ingreso', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/salida', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'contract.bound'])->group(function (): void {
    Route::get('/tablero', DashboardController::class)->name('dashboard');
    Route::get('/personal', [PersonController::class, 'index'])->name('people.index');
    Route::post('/personal/importar', [PersonController::class, 'import'])->name('people.import');
    Route::get('/personal/{person}', [PersonController::class, 'show'])->name('people.show');
    Route::get('/documentos', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documentos/{document}/descarga', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/parafiscales', [ParafiscalController::class, 'index'])->name('parafiscals.index');
    Route::get('/electronica', [MaintenanceController::class, 'index'])->name('electronics.index');
    Route::post('/electronica/mantenimientos', [MaintenanceController::class, 'store'])->name('maintenances.store');
    Route::get('/servicios', [ServiceDeliveryController::class, 'index'])->name('services.index');
    Route::get('/novedades', [NoveltyController::class, 'index'])->name('novelties.index');
    Route::post('/novedades', [NoveltyController::class, 'store'])->name('novelties.store');
});
