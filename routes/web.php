<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ProteinCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::prefix('proteins')->name('proteins.')->group(function () {
    Route::get('/', [ProteinCatalogController::class, 'index'])->name('index');
    Route::get('/{proteinId}', [ProteinCatalogController::class, 'show'])->name('show');
});

Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/submit', [JobController::class, 'create'])->name('create');
    Route::post('/', [JobController::class, 'store'])->name('store');
    Route::get('/{jobId}', [JobController::class, 'show'])->name('show');
});

Route::prefix('api')->name('api.')->group(function () {
    Route::get('/jobs/{jobId}/status', Api\JobStatusController::class)->name('jobs.status');
    Route::get('/jobs/{jobId}/outputs', Api\JobOutputsController::class)->name('jobs.outputs');
    Route::get('/jobs/{jobId}/accounting', Api\JobAccountingController::class)->name('jobs.accounting');
});
