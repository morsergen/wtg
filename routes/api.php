<?php

use App\Http\Controllers\Api\ImportController;
use Illuminate\Support\Facades\Route;

Route::controller(ImportController::class)
    ->name('imports.')
    ->prefix('imports')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::get('/{import}', 'show')->name('show');
    });
