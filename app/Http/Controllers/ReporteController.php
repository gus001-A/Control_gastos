<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use App\Models\Deuda;
use App\Models\Movimiento;
use App\Models\Proyecto;
use App\Support\Fechas;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function index(): Response
    {
        $userId = Auth::id();
        $anio = (int) request('anio', now()->year);

        $porMesRaw = Movimiento::where('user_id', $userId)
            ->whereBetween('fecha', ["{$anio}-01-01", "{$anio}-12-31"])
            ->selectRaw('CAST(strftime(\'%m\', fecha) AS INTEGER) as mes')
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) as ingresos")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'gasto' THEN monto ELSE 0 END), 0) as gastos")
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        // La expresión strftime es de SQLite; en Postgres se sustituye por EXTRACT(MONTH FROM fecha).
        if (config('database.default') !== 'sqlite') {
            $porMesRaw = Movimiento::where('user_id', $userId)
                ->whereBetween('fecha', ["{$anio}-01-01", "{$anio}-12-31"])
                ->selectRaw('EXTRACT(MONTH FROM fecha)::int as mes')
                ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) as ingresos")
                ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'gasto' THEN monto ELSE 0 END), 0) as gastos")
                ->groupBy('mes')
                ->get()
                ->keyBy('mes');
        }

        $porMes = [];
        for ($m = 1; $m <= 12; $m++) {
            $fila = $porMesRaw->get($m);
            $porMes[] = [
                'mes' => $m,
                'nombre' => Fechas::mesAbrev($m),
                'ingresos' => $fila ? round((float) $fila->ingresos, 2) : 0,
                'gastos' => $fila ? round((float) $fila->gastos, 2) : 0,
            ];
        }

        $evolucionSaldo = $this->evolucionSaldo($userId, 6);

        $proyectos = Auth::user()->proyectos()->withSum('pagos', 'monto')->get()->map(fn ($p) => [
            'nombre' => $p->nombre,
            'cobrado' => (float) $p->monto_cobrado,
            'pagado' => $p->pagado,
            'pendiente' => $p->pendiente,
            'estado' => $p->estado,
        ]);

        // 👇 Deudas individuales (para la gráfica de barras)
        $deudasModel = Auth::user()->deudas()
            ->withSum('abonos', 'monto')
            ->withSum('aumentosDeuda', 'monto')
            ->get();

        $deudas = $deudasModel->map(fn ($d) => [
            'id' => $d->id,
            'nombre' => $d->nombre ?? $d->descripcion ?? 'Deuda #'.$d->id,
            'total' => round((float) $d->montoTotalEfectivo, 2),
            'pagado' => round((float) $d->pagado, 2),
            'restante' => round((float) $d->restante, 2),
            'estado' => $d->estado,
        ])->values();

        // Resumen agregado (para las stats y la barra de progreso global)
        $resumenDeudas = [
            'total' => round($deudasModel->sum('montoTotalEfectivo'), 2),
            'pagado' => round($deudasModel->sum('pagado'), 2),
            'restante' => round($deudasModel->sum('restante'), 2),
            'activas' => $deudasModel->where('estado', 'activa')->count(),
            'totalDeudas' => $deudasModel->count(),
        ];

        return Inertia::render('Reportes/Index', [
            'anio' => $anio,
            'anios' => range(now()->year, now()->year - 5),
            'porMes' => $porMes,
            'evolucionSaldo' => $evolucionSaldo,
            'proyectos' => $proyectos,
            'resumenDeudas' => $resumenDeudas,
            'deudas' => $deudas, // 👈 nuevo prop
        ]);
    }

    private function evolucionSaldo(int $userId, int $meses): array
    {
        $saldoInicialTotal = (float) Cuenta::where('user_id', $userId)->sum('saldo_inicial');

        $movimientos = Movimiento::where('user_id', $userId)
            ->orderBy('fecha')
            ->get(['fecha', 'tipo', 'monto']);

        $hoy = now();
        $puntos = [];
        $cursor = $hoy->copy()->startOfMonth();
        $fechasCorte = [];
        for ($i = 0; $i < $meses; $i++) {
            $fechasCorte[] = $cursor->copy();
            $cursor->subMonth();
        }
        $fechasCorte = array_reverse($fechasCorte);

        foreach ($fechasCorte as $fecha) {
            $corte = $fecha->copy()->endOfMonth()->toDateString();
            $saldo = $saldoInicialTotal;
            foreach ($movimientos as $mov) {
                if ($mov->fecha->toDateString() <= $corte) {
                    $saldo += $mov->tipo === 'ingreso' ? (float) $mov->monto : -(float) $mov->monto;
                }
            }
            $puntos[] = [
                'anio' => $fecha->year,
                'mes' => $fecha->month,
                'etiqueta' => Fechas::mesAbrev($fecha->month).' '.$fecha->year,
                'saldo' => round($saldo, 2),
            ];
        }

        return $puntos;
    }
}