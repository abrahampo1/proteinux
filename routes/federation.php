<?php

use App\Http\Controllers\Federation\InboxController;
use App\Http\Controllers\Federation\InstanceInfoController;
use App\Http\Controllers\Federation\PeeringController;
use Illuminate\Support\Facades\Route;

Route::prefix('federation')->name('federation.')->middleware('federation.enabled')->group(function () {
    Route::get('/instance-info', InstanceInfoController::class)->name('instance-info')->withoutMiddleware('federation.enabled');

    Route::post('/inbox', InboxController::class)->name('inbox')->middleware('federation.signature');

    Route::prefix('peering')->name('peering.')->middleware('federation.signature')->group(function () {
        Route::post('/request', [PeeringController::class, 'request'])->name('request');
        Route::post('/accept', [PeeringController::class, 'accept'])->name('accept');
        Route::post('/reject', [PeeringController::class, 'reject'])->name('reject');
    });
});
