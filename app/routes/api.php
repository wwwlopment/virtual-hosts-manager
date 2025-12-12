<?php

use App\Http\Controllers\Api\NginxController;
use App\Http\Controllers\Api\VirtualHostController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Virtual Hosts
    Route::get('/hosts', [VirtualHostController::class, 'index']);
    Route::post('/hosts', [VirtualHostController::class, 'store']);
    Route::patch('/hosts/{host}/status', [VirtualHostController::class, 'updateStatus']);
    Route::delete('/hosts/{host}', [VirtualHostController::class, 'destroy']);
    
    // Nginx Management
    Route::prefix('nginx')->group(function () {
        Route::get('/status', [NginxController::class, 'status']);
        Route::post('/start', [NginxController::class, 'start']);
        Route::post('/stop', [NginxController::class, 'stop']);
        Route::post('/restart', [NginxController::class, 'restart']);
        Route::post('/reload', [NginxController::class, 'reload']);
    });
});
