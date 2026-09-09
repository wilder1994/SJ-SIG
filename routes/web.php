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
    Route::get('/personal/nuevo', [PersonController::class, 'create'])->name('people.create');
    Route::post('/personal', [PersonController::class, 'store'])->name('people.store');
    Route::post('/personal/importar', [PersonController::class, 'import'])->name('people.import');
    Route::get('/personal/{person}', [PersonController::class, 'show'])->name('people.show');
    Route::get('/documentos', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documentos/carpeta/{person}', [DocumentController::class, 'folder'])->name('documents.folder');
    Route::post('/documentos/carpeta/{person}', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/documentos/carpeta/{person}/cursos', [DocumentController::class, 'storeCourse'])->name('documents.courses.store');
    Route::get('/documentos/archivo/{document}/ver', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documentos/archivo/{document}/descarga', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/parafiscales', [ParafiscalController::class, 'index'])->name('parafiscals.index');
    Route::post('/parafiscales', [ParafiscalController::class, 'store'])->name('parafiscals.store');
    Route::get('/parafiscales/{parafiscal}/ver', [ParafiscalController::class, 'preview'])->name('parafiscals.preview');
    Route::get('/parafiscales/{parafiscal}/descarga', [ParafiscalController::class, 'download'])->name('parafiscals.download');
    Route::get('/electronica', [MaintenanceController::class, 'index'])->name('electronics.index');
    Route::post('/electronica/mantenimientos', [MaintenanceController::class, 'store'])->name('maintenances.store');
    Route::get('/servicios', [ServiceDeliveryController::class, 'index'])->name('services.index');
    Route::get('/novedades', [NoveltyController::class, 'index'])->name('novelties.index');
    Route::post('/novedades', [NoveltyController::class, 'store'])->name('novelties.store');
});
