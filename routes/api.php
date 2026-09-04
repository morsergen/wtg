<?php

use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\PropertyController;
use Illuminate\Support\Facades\Route;

Route::controller(ImportController::class)
    ->name('imports.')
    ->prefix('imports')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::get('/{import}', 'show')->name('show');
    });

Route::controller(PropertyController::class)
    ->name('properties.')
    ->prefix('properties')
    ->group(function () {
        Route::get('/', 'index')->name('index');
    });
