<?php

use App\Http\Controllers\AbonoDeudaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeudaController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\PagoProyectoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TransferenciaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/cuentas', [CuentaController::class, 'index'])->name('cuentas.index');
    Route::post('/cuentas', [CuentaController::class, 'store'])->name('cuentas.store');
    Route::put('/cuentas/{cuenta}', [CuentaController::class, 'update'])->name('cuentas.update');
    Route::delete('/cuentas/{cuenta}', [CuentaController::class, 'destroy'])->name('cuentas.destroy');

    Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');
    Route::post('/movimientos', [MovimientoController::class, 'store'])->name('movimientos.store');
    Route::put('/movimientos/{movimiento}', [MovimientoController::class, 'update'])->name('movimientos.update');
    Route::delete('/movimientos/{movimiento}', [MovimientoController::class, 'destroy'])->name('movimientos.destroy');

    Route::post('/transferencias', [TransferenciaController::class, 'store'])->name('transferencias.store');
    Route::delete('/transferencias/{transferencia}', [TransferenciaController::class, 'destroy'])->name('transferencias.destroy');

    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

    Route::get('/deudas', [DeudaController::class, 'index'])->name('deudas.index');
    Route::get('/deudas/{deuda}', [DeudaController::class, 'show'])->name('deudas.show');
    Route::post('/deudas', [DeudaController::class, 'store'])->name('deudas.store');
    Route::put('/deudas/{deuda}', [DeudaController::class, 'update'])->name('deudas.update');
    Route::delete('/deudas/{deuda}', [DeudaController::class, 'destroy'])->name('deudas.destroy');
    Route::post('/deudas/{deuda}/abonos', [AbonoDeudaController::class, 'store'])->name('abonos.store');
    Route::delete('/abonos/{abono}', [AbonoDeudaController::class, 'destroy'])->name('abonos.destroy');

    Route::get('/proyectos', [ProyectoController::class, 'index'])->name('proyectos.index');
    Route::get('/proyectos/{proyecto}', [ProyectoController::class, 'show'])->name('proyectos.show');
    Route::post('/proyectos', [ProyectoController::class, 'store'])->name('proyectos.store');
    Route::put('/proyectos/{proyecto}', [ProyectoController::class, 'update'])->name('proyectos.update');
    Route::delete('/proyectos/{proyecto}', [ProyectoController::class, 'destroy'])->name('proyectos.destroy');
    Route::post('/proyectos/{proyecto}/pagos', [PagoProyectoController::class, 'store'])->name('pagos.store');
    Route::delete('/pagos/{pago}', [PagoProyectoController::class, 'destroy'])->name('pagos.destroy');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
