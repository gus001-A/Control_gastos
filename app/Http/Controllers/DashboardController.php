<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Deuda;
use App\Models\Movimiento;
use App\Models\Proyecto;
use App\Support\Fechas;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $userId = Auth::id();
        $hoy = now();
        $inicioMes = $hoy->copy()->startOfMonth();

        $cuentas = Auth::user()->cuentas()->where('activa', true)->orderBy('nombre')->get();
        $saldoTotal = round($cuentas->sum('saldo'), 2);

        $movMes = Movimiento::where('user_id', $userId)
            ->whereBetween('fecha', [$inicioMes->toDateString(), $hoy->toDateString()])
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) as ingresos")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'gasto' THEN monto ELSE 0 END), 0) as gastos")
            ->first();

        $deudasActivas = Deuda::where('user_id', $userId)->where('estado', 'activa')->get();
        $proyectosActivos = Proyecto::where('user_id', $userId)->where('estado', 'activo')->get();

        $gastosPorCategoria = Movimiento::where('movimientos.user_id', $userId)
            ->where('movimientos.tipo', 'gasto')
            ->whereBetween('movimientos.fecha', [$inicioMes->toDateString(), $hoy->toDateString()])
            ->leftJoin('categorias', 'categorias.id', '=', 'movimientos.categoria_id')
            ->selectRaw("COALESCE(categorias.nombre, 'Sin categoría') as categoria")
            ->selectRaw("COALESCE(categorias.color, '#94a3b8') as color")
            ->selectRaw('SUM(movimientos.monto) as total')
            ->groupBy('categoria', 'color')
            ->orderByDesc('total')
            ->get();

        $ultimosMovimientos = Movimiento::where('user_id', $userId)
            ->with(['cuenta:id,nombre', 'categoria:id,nombre,color'])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'resumen' => [
                'saldoTotal' => $saldoTotal,
                'ingresosMes' => round((float) $movMes->ingresos, 2),
                'gastosMes' => round((float) $movMes->gastos, 2),
                'deudaPendiente' => round($deudasActivas->sum('restante'), 2),
                'numDeudasActivas' => $deudasActivas->count(),
                'proyectosPorCobrar' => round($proyectosActivos->sum('pendiente'), 2),
                'numProyectosActivos' => $proyectosActivos->count(),
            ],
            'cuentas' => $cuentas,
            'gastosPorCategoria' => $gastosPorCategoria,
            'ultimosMovimientos' => $ultimosMovimientos,
            'mesActual' => Fechas::nombreMes((int) $hoy->format('n')).' '.$hoy->format('Y'),
        ]);
    }
}
