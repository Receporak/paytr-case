<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\POSController;


Route::prefix('v1')->group(function () {
    Route::prefix('pos')->group(function () {
        Route::post('/select', [POSController::class, 'select']);
        Route::post('/rates-sync', [POSController::class, 'rateSync']);
    });

});
