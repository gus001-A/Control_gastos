<?php

namespace App\Http\Controllers;

use App\Models\AbonoDeuda;
use App\Models\Deuda;
use App\Models\Movimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbonoDeudaController extends Controller
{
    public function store(Request $request, Deuda $deuda): RedirectResponse
    {
        abort_unless($deuda->user_id === Auth::id(), 403);

        $datos = $request->validate([
            'cuenta_id' => 'nullable|exists:cuentas,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'notas' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($datos, $deuda) {
            $movimientoId = null;

            if (! empty($datos['cuenta_id'])) {
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
                $movimientoId = $movimiento->id;
            }

            AbonoDeuda::create([
                'deuda_id' => $deuda->id,
                'cuenta_id' => $datos['cuenta_id'] ?? null,
                'movimiento_id' => $movimientoId,
                'monto' => $datos['monto'],
                'fecha' => $datos['fecha'],
                'notas' => $datos['notas'] ?? null,
            ]);
        });

        $deuda->refresh();
        $deuda->revisarEstado();

        return back()->with('success', 'Abono registrado.');
    }

    public function destroy(AbonoDeuda $abono): RedirectResponse
    {
        $deuda = $abono->deuda;
        abort_unless($deuda->user_id === Auth::id(), 403);

        DB::transaction(function () use ($abono) {
            $abono->movimiento?->delete();
            $abono->delete();
        });

        $deuda->refresh();
        $deuda->revisarEstado();

        return back()->with('success', 'Abono eliminado.');
    }
}
