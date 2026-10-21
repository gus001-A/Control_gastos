<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidaSaldoCuenta;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\Transferencia;
use App\Support\Fechas;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MovimientoController extends Controller
{
    use ValidaSaldoCuenta;

    /** Orígenes que no se pueden editar/eliminar directo porque otra pantalla lleva su cuenta (deudas, proyectos). */
    private const ORIGENES_BLOQUEADOS = ['abono_deuda', 'aumento_deuda', 'pago_proyecto'];

    public function index(Request $request): Response
    {
        $userId = Auth::id();

        // Mes seleccionado (por defecto el mes actual).
        // Si llega vacío ('') desde el frontend, significa "ver todos los meses".
        $mes = $request->input('mes', now()->format('Y-m'));
        $filtrarPorMes = ! empty($mes);

        // Rango de fechas (solo si se filtra por mes)
        $inicio = null;
        $fin = null;

        if ($filtrarPorMes) {
            try {
                $inicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth()->toDateString();
                $fin = Carbon::createFromFormat('Y-m', $mes)->endOfMonth()->toDateString();
            } catch (\Exception $e) {
                // Si el formato es inválido, no filtramos por mes
                $filtrarPorMes = false;
            }
        }

        // ---------------------------------------------------------------------
        // Movimientos
        // ---------------------------------------------------------------------
        $query = Movimiento::where('user_id', $userId)
            ->with([
                'cuenta:id,nombre,color,tipo',
                'categoria:id,nombre,color',
                'deuda:id,nombre',
                'proyecto:id,nombre',
            ]);

        if ($request->filled('cuenta_id')) {
            $query->where('cuenta_id', $request->integer('cuenta_id'));
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->integer('categoria_id'));
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo'));
        }

        // Filtro por mes (solo si se pidió un mes específico)
        if ($filtrarPorMes) {
            $query->whereBetween('fecha', [$inicio, $fin]);
        }

        // Compatibilidad opcional: fecha_inicio / fecha_fin manuales
        if ($request->filled('fecha_inicio')) {
            $query->where('fecha', '>=', $request->string('fecha_inicio'));
        }
        if ($request->filled('fecha_fin')) {
            $query->where('fecha', '<=', $request->string('fecha_fin'));
        }

        $totales = (clone $query)
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) as ingresos")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'gasto' THEN monto ELSE 0 END), 0) as gastos")
            ->first();

        $movimientos = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // ---------------------------------------------------------------------
        // Transferencias (mismo filtro de mes, si aplica)
        // ---------------------------------------------------------------------
        $transferenciasQuery = Transferencia::where('user_id', $userId)
            ->with([
                'cuentaOrigen:id,nombre,color',
                'cuentaDestino:id,nombre,color',
            ]);

        if ($filtrarPorMes) {
            $transferenciasQuery->whereBetween('fecha', [$inicio, $fin]);
        }

        if ($request->filled('fecha_inicio')) {
            $transferenciasQuery->where('fecha', '>=', $request->string('fecha_inicio'));
        }
        if ($request->filled('fecha_fin')) {
            $transferenciasQuery->where('fecha', '<=', $request->string('fecha_fin'));
        }

        $transferenciasList = $transferenciasQuery
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'transferencias_page')
            ->withQueryString();

        // ---------------------------------------------------------------------
        // Render
        // ---------------------------------------------------------------------
        return Inertia::render('Movimientos/Index', [
            'movimientos' => $movimientos,
            'transferencias' => $transferenciasList,

            // Se quita el array de columnas para que `conSaldo()` pueda agregar `saldo`
            'cuentas' => Auth::user()
                ->cuentas()
                ->conSaldo()
                ->orderBy('nombre')
                ->get(),

            'categorias' => Auth::user()
                ->categorias()
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->get(),

            'filtros' => [
                'cuenta_id' => $request->input('cuenta_id', ''),
                'categoria_id' => $request->input('categoria_id', ''),
                'tipo' => $request->input('tipo', ''),
                // ⚠️ Importante: enviar '' cuando se pide "todos los meses",
                // para que el frontend sepa que NO hay filtro de mes.
                'mes' => $mes,
            ],

            'totales' => [
                'ingresos' => round((float) $totales->ingresos, 2),
                'gastos' => round((float) $totales->gastos, 2),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);

        if ($datos['tipo'] === 'gasto') {
            $this->asegurarFondos(Cuenta::findOrFail($datos['cuenta_id']), (float) $datos['monto']);
        }

        $datos['user_id'] = Auth::id();
        $datos['origen'] = 'manual';
        Movimiento::create($datos);

        return back()->with('success', 'Movimiento registrado.');
    }

    public function update(Request $request, Movimiento $movimiento): RedirectResponse
    {
        $this->autorizar($movimiento);

        if (in_array($movimiento->origen, self::ORIGENES_BLOQUEADOS, true)) {
            return back()->with('error', 'Este movimiento se generó automáticamente; edítalo desde deudas o proyectos.');
        }

        $datos = $this->validarDatos($request);

        if ($datos['tipo'] === 'gasto') {
            $this->asegurarFondos(Cuenta::findOrFail($datos['cuenta_id']), (float) $datos['monto'], $movimiento);
        }

        $movimiento->update($datos);

        return back()->with('success', 'Movimiento actualizado.');
    }

    public function destroy(Movimiento $movimiento): RedirectResponse
    {
        $this->autorizar($movimiento);

        if (in_array($movimiento->origen, self::ORIGENES_BLOQUEADOS, true)) {
            return back()->with('error', 'Este movimiento viene de un abono o pago; elimínalo desde esa sección.');
        }

        $movimiento->delete();

        return back()->with('success', 'Movimiento eliminado.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'cuenta_id' => 'required|exists:cuentas,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'tipo' => 'required|in:ingreso,gasto',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => Fechas::REGLA_NO_FUTURA,
            'descripcion' => 'nullable|string|max:255',
        ], Fechas::MENSAJES_NO_FUTURA);
    }

    private function autorizar(Movimiento $movimiento): void
    {
        abort_unless($movimiento->user_id === Auth::id(), 403);
    }
}