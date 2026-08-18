<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Catalogs\FieldController;
use App\Http\Controllers\Catalogs\MachineryController;
use App\Http\Controllers\Catalogs\MachineryTypeController;
use App\Http\Controllers\Catalogs\StatusController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Security\PermissionController;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\SecurityUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::redirect('/', '/configuracion/campos')->name('home');

    // Ver el catálogo requiere catalogs.view; crear/editar/activar/eliminar
    // requiere además catalogs.manage (aplicado por ruta, no al grupo).
    Route::prefix('configuracion')->name('catalogs.')->middleware('permission:catalogs.view')->group(function () {
        Route::prefix('campos')->name('field.')->group(function () {
            Route::get('/', [FieldController::class, 'index'])->name('index');
            Route::post('/', [FieldController::class, 'store'])->name('store')->middleware('permission:catalogs.manage');
            Route::put('/{id}', [FieldController::class, 'update'])->name('update')->middleware('permission:catalogs.manage');
            Route::delete('/{id}', [FieldController::class, 'destroy'])->name('destroy')->middleware('permission:catalogs.manage');
            Route::patch('/{id}/estado', [FieldController::class, 'toggleStatus'])->name('toggle')->middleware('permission:catalogs.manage');
        });

        Route::prefix('estados')->name('status.')->group(function () {
            Route::get('/', [StatusController::class, 'index'])->name('index');
            Route::post('/', [StatusController::class, 'store'])->name('store')->middleware('permission:catalogs.manage');
            Route::put('/{id}', [StatusController::class, 'update'])->name('update')->middleware('permission:catalogs.manage');
            Route::delete('/{id}', [StatusController::class, 'destroy'])->name('destroy')->middleware('permission:catalogs.manage');
            Route::patch('/{id}/estado', [StatusController::class, 'toggleStatus'])->name('toggle')->middleware('permission:catalogs.manage');
        });

        Route::prefix('tipos-maquinaria')->name('machinery-type.')->group(function () {
            Route::get('/', [MachineryTypeController::class, 'index'])->name('index');
            Route::post('/', [MachineryTypeController::class, 'store'])->name('store')->middleware('permission:catalogs.manage');
            Route::put('/{id}', [MachineryTypeController::class, 'update'])->name('update')->middleware('permission:catalogs.manage');
            Route::delete('/{id}', [MachineryTypeController::class, 'destroy'])->name('destroy')->middleware('permission:catalogs.manage');
            Route::patch('/{id}/estado', [MachineryTypeController::class, 'toggleStatus'])->name('toggle')->middleware('permission:catalogs.manage');
        });

        Route::prefix('maquinaria')->name('machinery.')->group(function () {
            Route::get('/', [MachineryController::class, 'index'])->name('index');
            Route::get('/opciones', [MachineryController::class, 'options'])->name('options');
            Route::post('/', [MachineryController::class, 'store'])->name('store')->middleware('permission:catalogs.manage');
            Route::put('/{id}', [MachineryController::class, 'update'])->name('update')->middleware('permission:catalogs.manage');
            Route::delete('/{id}', [MachineryController::class, 'destroy'])->name('destroy')->middleware('permission:catalogs.manage');
            Route::patch('/{id}/estado', [MachineryController::class, 'toggleStatus'])->name('toggle')->middleware('permission:catalogs.manage');
        });
    });

    // Datos JSON del flujo de Reportar — registrados antes del catch-all de
    // abajo para que no lo intercepte.
    Route::prefix('reportar/datos')->name('report.data.')->middleware('permission:report.create')->group(function () {
        Route::get('/yardas', [ReportController::class, 'index'])->name('fields');
        Route::get('/yardas/{field}/tipos', [ReportController::class, 'machineryTypes'])->name('machinery-types');
        Route::get('/yardas/{field}/tipos/{machineryType}/maquinas', [ReportController::class, 'machines'])->name('machines');
        Route::get('/estados', [ReportController::class, 'statuses'])->name('statuses');
        Route::post('/registrar', [ReportController::class, 'store'])->name('store');
    });

    // Isla Vue con router interno (Yarda → Tipo → Máquina → Registrar):
    // cualquier sub-ruta sirve el mismo shell para que recargar la página
    // en un paso intermedio no rompa (catch-all, va al final).
    Route::get('/reportar/{any?}', [ReportController::class, 'index'])
        ->where('any', '.*')
        ->middleware('permission:report.create')
        ->name('report.index');

    Route::prefix('dashboard/datos')->name('dashboard.data.')->middleware('permission:dashboard.view')->group(function () {
        Route::get('/resumen', [DashboardController::class, 'summary'])->name('summary');
        Route::get('/reportes', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/grafica', [DashboardController::class, 'chart'])->name('chart');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard.index');

    Route::prefix('seguridad')->name('security.')->middleware('permission:security.manage')->group(function () {
        Route::redirect('/', '/seguridad/roles')->name('index');

        Route::prefix('roles')->name('role.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
            Route::patch('/{role}/permisos', [RoleController::class, 'updatePermissions'])->name('permissions');
        });

        Route::prefix('permisos')->name('permission.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('usuarios')->name('user.')->group(function () {
            Route::get('/', [SecurityUserController::class, 'index'])->name('index');
            Route::post('/', [SecurityUserController::class, 'store'])->name('store');
            Route::patch('/{user}/rol', [SecurityUserController::class, 'updateRole'])->name('role');
        });
    });
});
