<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\ScheduledJobController;
use Illuminate\Support\Facades\Route;


Route::prefix('health')->group(function () {
    Route::get('/', [HealthCheckController::class, 'live'])->name('health.live');
    Route::get('/ready', [HealthCheckController::class, 'ready'])->name('health.ready');
});

Route::get('/jobs', [ScheduledJobController::class, 'index']);
Route::post('/jobs/{id}/run', [ScheduledJobController::class, 'run']);
