<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Catalogs\FieldController;
use App\Http\Controllers\Catalogs\MachineryController;
use App\Http\Controllers\Catalogs\MachineryTypeController;
use App\Http\Controllers\Catalogs\StatusController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::redirect('/', '/configuracion/campos')->name('home');

    Route::prefix('configuracion')->name('catalogs.')->group(function () {
        Route::prefix('campos')->name('field.')->group(function () {
            Route::get('/', [FieldController::class, 'index'])->name('index');
            Route::post('/', [FieldController::class, 'store'])->name('store');
            Route::put('/{id}', [FieldController::class, 'update'])->name('update');
            Route::delete('/{id}', [FieldController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/estado', [FieldController::class, 'toggleStatus'])->name('toggle');
        });

        Route::prefix('estados')->name('status.')->group(function () {
            Route::get('/', [StatusController::class, 'index'])->name('index');
            Route::post('/', [StatusController::class, 'store'])->name('store');
            Route::put('/{id}', [StatusController::class, 'update'])->name('update');
            Route::delete('/{id}', [StatusController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/estado', [StatusController::class, 'toggleStatus'])->name('toggle');
        });

        Route::prefix('tipos-maquinaria')->name('machinery-type.')->group(function () {
            Route::get('/', [MachineryTypeController::class, 'index'])->name('index');
            Route::post('/', [MachineryTypeController::class, 'store'])->name('store');
            Route::put('/{id}', [MachineryTypeController::class, 'update'])->name('update');
            Route::delete('/{id}', [MachineryTypeController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/estado', [MachineryTypeController::class, 'toggleStatus'])->name('toggle');
        });

        Route::prefix('maquinaria')->name('machinery.')->group(function () {
            Route::get('/', [MachineryController::class, 'index'])->name('index');
            Route::get('/opciones', [MachineryController::class, 'options'])->name('options');
            Route::post('/', [MachineryController::class, 'store'])->name('store');
            Route::put('/{id}', [MachineryController::class, 'update'])->name('update');
            Route::delete('/{id}', [MachineryController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/estado', [MachineryController::class, 'toggleStatus'])->name('toggle');
        });
    });
});
