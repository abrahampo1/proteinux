<?php

use App\Http\Controllers\Admin\FederationAdminController;
use App\Http\Controllers\AiSettingsController;
use App\Http\Controllers\Api;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Forum\ForumPostController;
use App\Http\Controllers\Forum\ForumThreadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobLibraryController;
use App\Http\Controllers\ProteinCatalogController;
use App\Http\Controllers\Scientific\ScientificDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/registro', [RegisterController::class, 'create'])->name('register');
    Route::post('/registro', [RegisterController::class, 'store']);
    Route::get('/acceso', [LoginController::class, 'create'])->name('login');
    Route::post('/acceso', [LoginController::class, 'store']);
});
Route::post('/salir', LogoutController::class)->name('logout')->middleware('auth');

Route::prefix('proteins')->name('proteins.')->group(function () {
    Route::get('/', [ProteinCatalogController::class, 'index'])->name('index');
    Route::get('/{proteinId}', [ProteinCatalogController::class, 'show'])->name('show');
});

Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/submit', [JobController::class, 'create'])->name('create');
    Route::post('/', [JobController::class, 'store'])->name('store')->middleware('auth');
    Route::get('/{jobId}', [JobController::class, 'show'])->name('show');
});

Route::get('/biblioteca', [JobLibraryController::class, 'index'])->name('library.index');
Route::middleware('auth')->group(function () {
    Route::post('/biblioteca/{predictedJob}/rerun', [JobLibraryController::class, 'rerun'])->name('library.rerun');
    Route::post('/biblioteca/{predictedJob}/compartir', [JobLibraryController::class, 'share'])->name('library.share');
});

Route::get('/ajustes-ia', [AiSettingsController::class, 'edit'])->name('settings.ai');
Route::post('/ajustes-ia', [AiSettingsController::class, 'update'])->name('settings.ai.update');
Route::delete('/ajustes-ia/{provider}', [AiSettingsController::class, 'destroy'])->name('settings.ai.destroy');

// Forum
Route::prefix('foro')->name('forum.')->group(function () {
    Route::get('/', [ForumThreadController::class, 'index'])->name('index');
    Route::get('/nuevo', [ForumThreadController::class, 'create'])->name('create')->middleware('auth');
    Route::post('/', [ForumThreadController::class, 'store'])->name('store')->middleware('auth');
    Route::get('/{thread:slug}', [ForumThreadController::class, 'show'])->name('show');
    Route::post('/{thread:slug}/respuestas', [ForumPostController::class, 'store'])->name('posts.store')->middleware('auth');
    Route::delete('/respuestas/{post}', [ForumPostController::class, 'destroy'])->name('posts.destroy')->middleware('auth');
});

// Scientific Documents
Route::prefix('documentos')->name('documents.')->group(function () {
    Route::get('/', [ScientificDocumentController::class, 'index'])->name('index');
    Route::get('/nuevo', [ScientificDocumentController::class, 'create'])->name('create')->middleware('auth');
    Route::post('/', [ScientificDocumentController::class, 'store'])->name('store')->middleware('auth');
    Route::get('/{document}', [ScientificDocumentController::class, 'show'])->name('show');
    Route::get('/{document}/descargar', [ScientificDocumentController::class, 'download'])->name('download');
});

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_admin'])->group(function () {
    Route::get('/federacion', [FederationAdminController::class, 'index'])->name('federation.index');
    Route::post('/federacion/conectar', [FederationAdminController::class, 'connect'])->name('federation.connect');
    Route::post('/federacion/{instance}/aceptar', [FederationAdminController::class, 'accept'])->name('federation.accept');
    Route::post('/federacion/{instance}/rechazar', [FederationAdminController::class, 'reject'])->name('federation.reject');
    Route::delete('/federacion/{instance}', [FederationAdminController::class, 'destroy'])->name('federation.destroy');
});

Route::prefix('api')->name('api.')->group(function () {
    Route::get('/jobs/{jobId}/status', Api\JobStatusController::class)->name('jobs.status');
    Route::get('/jobs/{jobId}/outputs', Api\JobOutputsController::class)->name('jobs.outputs');
    Route::get('/jobs/{jobId}/accounting', Api\JobAccountingController::class)->name('jobs.accounting');
    Route::get('/jobs/{jobId}/ai-analysis', Api\JobAiAnalysisController::class)->name('jobs.ai.analysis');
    Route::post('/jobs/{jobId}/ai-chat', Api\JobAiChatController::class)->name('jobs.ai.chat');
});
