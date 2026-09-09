<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForcedPasswordController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NoveltyController;
use App\Http\Controllers\OperationsTeamController;
use App\Http\Controllers\ParafiscalController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PlatformUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
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

Route::middleware(['auth', 'user.active'])->group(function (): void {
    Route::get('/clave', [ForcedPasswordController::class, 'edit'])->name('password.edit');
    Route::post('/clave', [ForcedPasswordController::class, 'update'])->name('password.update');
});

Route::middleware(['auth', 'user.active', 'password.changed', 'contract.bound', 'contract.required', 'tech.modules'])->group(function (): void {
    Route::get('/tablero', DashboardController::class)->name('dashboard');
    Route::get('/perfil', ProfileController::class)->name('profile.show');
    Route::get('/usuarios/foto/{user}', [PlatformUserController::class, 'photo'])->name('users.photo');
    Route::get('/clientes', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clientes/nuevo', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clientes', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clientes/{client}/editar', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clientes/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::get('/usuarios', [PlatformUserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/nuevo', [PlatformUserController::class, 'create'])->name('users.create');
    Route::post('/usuarios', [PlatformUserController::class, 'store'])->name('users.store');
    Route::get('/usuarios/{user}/editar', [PlatformUserController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{user}', [PlatformUserController::class, 'update'])->name('users.update');
    Route::get('/equipo', OperationsTeamController::class)->name('operations.index');
    Route::get('/instalaciones', [SiteController::class, 'index'])->name('sites.index');
    Route::post('/instalaciones', [SiteController::class, 'store'])->name('sites.store');
    Route::post('/instalaciones/{site}/puestos', [SiteController::class, 'storePost'])->name('sites.posts.store');
    Route::get('/personal', [PersonController::class, 'index'])->name('people.index');
    Route::get('/personal/nuevo', [PersonController::class, 'create'])->name('people.create');
    Route::post('/personal', [PersonController::class, 'store'])->name('people.store');
    Route::post('/personal/importar', [PersonController::class, 'import'])->name('people.import');
    Route::get('/personal/{person}', [PersonController::class, 'show'])->name('people.show');
    Route::get('/documentos', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documentos/carpeta/{person}', [DocumentController::class, 'folder'])->name('documents.folder');
    Route::post('/documentos/carpeta/{person}', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/documentos/carpeta/{person}/historia', [DocumentController::class, 'storeHistoryBatch'])->name('documents.history.batch');
    Route::get('/documentos/carpeta/{person}/historia/{batch}', [DocumentController::class, 'historyIndex'])->name('documents.history.index');
    Route::get('/documentos/carpeta/{person}/historia/{batch}/ver', [DocumentController::class, 'previewBatch'])->name('documents.history.preview');
    Route::post('/documentos/carpeta/{person}/historia/{batch}', [DocumentController::class, 'storeHistoryIndex'])->name('documents.history.store');
    Route::post('/documentos/carpeta/{person}/historia-na', [DocumentController::class, 'markHistoryNa'])->name('documents.history.na');
    Route::post('/documentos/carpeta/{person}/afiliaciones', [DocumentController::class, 'storeAffiliationBatch'])->name('documents.affiliations.batch');
    Route::get('/documentos/carpeta/{person}/afiliaciones/{batch}', [DocumentController::class, 'affiliationIndex'])->name('documents.affiliations.index');
    Route::get('/documentos/carpeta/{person}/afiliaciones/{batch}/ver', [DocumentController::class, 'previewAffiliationBatch'])->name('documents.affiliations.preview');
    Route::post('/documentos/carpeta/{person}/afiliaciones/{batch}', [DocumentController::class, 'storeAffiliationIndex'])->name('documents.affiliations.store');
    Route::post('/documentos/carpeta/{person}/afiliaciones-na', [DocumentController::class, 'markAffiliationNa'])->name('documents.affiliations.na');
    Route::post('/documentos/carpeta/{person}/certificados', [DocumentController::class, 'storeCertificateBatch'])->name('documents.certificates.batch');
    Route::get('/documentos/carpeta/{person}/certificados/{batch}', [DocumentController::class, 'certificateIndex'])->name('documents.certificates.index');
    Route::get('/documentos/carpeta/{person}/certificados/{batch}/ver', [DocumentController::class, 'previewCertificateBatch'])->name('documents.certificates.preview');
    Route::post('/documentos/carpeta/{person}/certificados/{batch}', [DocumentController::class, 'storeCertificateIndex'])->name('documents.certificates.store');
    Route::post('/documentos/carpeta/{person}/certificados-na', [DocumentController::class, 'markCertificateNa'])->name('documents.certificates.na');
    Route::post('/documentos/carpeta/{person}/cursos', [DocumentController::class, 'storeCourseBatch'])->name('documents.courses.batch');
    Route::get('/documentos/carpeta/{person}/cursos/{batch}', [DocumentController::class, 'courseIndex'])->name('documents.courses.index');
    Route::get('/documentos/carpeta/{person}/cursos/{batch}/ver', [DocumentController::class, 'previewCourseBatch'])->name('documents.courses.preview');
    Route::post('/documentos/carpeta/{person}/cursos/{batch}', [DocumentController::class, 'storeCourseIndex'])->name('documents.courses.store');
    Route::post('/documentos/carpeta/{person}/cursos-na', [DocumentController::class, 'markCourseNa'])->name('documents.courses.na');
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
