<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidaSaldoCuenta;
use App\Models\AbonoDeuda;
use App\Models\Cuenta;
use App\Models\Deuda;
use App\Models\Movimiento;
use App\Support\Fechas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbonoDeudaController extends Controller
{
    use ValidaSaldoCuenta;

    public function store(Request $request, Deuda $deuda): RedirectResponse
    {
        abort_unless($deuda->user_id === Auth::id(), 403);

        $datos = $request->validate([
            'tipo' => 'required|in:abono,aumento',
            'cuenta_id' => 'nullable|exists:cuentas,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => Fechas::REGLA_NO_FUTURA,
            'notas' => 'nullable|string|max:255',
        ], Fechas::MENSAJES_NO_FUTURA);

        $esAumento = $datos['tipo'] === 'aumento';

        if (! $esAumento && ! empty($datos['cuenta_id'])) {
            $this->asegurarFondos(Cuenta::findOrFail($datos['cuenta_id']), (float) $datos['monto']);
        }

        DB::transaction(function () use ($datos, $deuda, $esAumento) {
            $movimientoId = null;

            if (! empty($datos['cuenta_id'])) {
                if ($esAumento) {
                    $categoria = Auth::user()->categorias()->where('nombre', 'Otros ingresos')->where('tipo', 'ingreso')->first();
                    $movimiento = Movimiento::create([
                        'user_id' => Auth::id(),
                        'cuenta_id' => $datos['cuenta_id'],
                        'categoria_id' => $categoria?->id,
                        'tipo' => 'ingreso',
                        'monto' => $datos['monto'],
                        'descripcion' => 'Aumento de deuda: '.$deuda->nombre,
                        'fecha' => $datos['fecha'],
                        'deuda_id' => $deuda->id,
                        'origen' => 'aumento_deuda',
                    ]);
                } else {
                    $categoria = Auth::user()->categorias()->where('nombre', 'Pago de deudas')->where('tipo', 'gasto')->first();
                    $movimiento = Movimiento::create([
                        'user_id' => Auth::id(),
                        'cuenta_id' => $datos['cuenta_id'],
                        'categoria_id' => $categoria?->id,
                        'tipo' => 'gasto',
                        'monto' => $datos['monto'],
                        'descripcion' => 'Abono a deuda: '.$deuda->nombre,
                        'fecha' => $datos['fecha'],
                        'deuda_id' => $deuda->id,
                        'origen' => 'abono_deuda',
                    ]);
                }
                $movimientoId = $movimiento->id;
            }

            AbonoDeuda::create([
                'deuda_id' => $deuda->id,
                'tipo' => $datos['tipo'],
                'cuenta_id' => $datos['cuenta_id'] ?? null,
                'movimiento_id' => $movimientoId,
                'monto' => $datos['monto'],
                'fecha' => $datos['fecha'],
                'notas' => $datos['notas'] ?? null,
            ]);
        });

        $deuda->refresh();
        $deuda->revisarEstado();

        return back()->with('success', $esAumento ? 'Aumento registrado.' : 'Abono registrado.');
    }

    public function destroy(AbonoDeuda $abono): RedirectResponse
    {
        $deuda = $abono->deuda;
        abort_unless($deuda->user_id === Auth::id(), 403);
        $esAumento = $abono->tipo === 'aumento';

        DB::transaction(function () use ($abono) {
            $abono->movimiento?->delete();
            $abono->delete();
        });

        $deuda->refresh();
        $deuda->revisarEstado();

        return back()->with('success', $esAumento ? 'Aumento eliminado.' : 'Abono eliminado.');
    }
}
