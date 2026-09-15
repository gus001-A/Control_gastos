<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\PagoProyecto;
use App\Models\Proyecto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagoProyectoController extends Controller
{
    public function store(Request $request, Proyecto $proyecto): RedirectResponse
    {
        abort_unless($proyecto->user_id === Auth::id(), 403);

        $datos = $request->validate([
            'cuenta_id' => 'nullable|exists:cuentas,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'notas' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($datos, $proyecto) {
            $movimientoId = null;

            if (! empty($datos['cuenta_id'])) {
                $categoria = Auth::user()->categorias()->where('nombre', 'Ingreso freelance')->where('tipo', 'ingreso')->first();

                $movimiento = Movimiento::create([
                    'user_id' => Auth::id(),
                    'cuenta_id' => $datos['cuenta_id'],
                    'categoria_id' => $categoria?->id,
                    'tipo' => 'ingreso',
                    'monto' => $datos['monto'],
                    'descripcion' => 'Pago de proyecto: '.$proyecto->nombre,
                    'fecha' => $datos['fecha'],
                    'proyecto_id' => $proyecto->id,
                    'origen' => 'pago_proyecto',
                ]);
                $movimientoId = $movimiento->id;
            }

            PagoProyecto::create([
                'proyecto_id' => $proyecto->id,
                'cuenta_id' => $datos['cuenta_id'] ?? null,
                'movimiento_id' => $movimientoId,
                'monto' => $datos['monto'],
                'fecha' => $datos['fecha'],
                'notas' => $datos['notas'] ?? null,
            ]);
        });

        return back()->with('success', 'Pago registrado.');
    }

    public function destroy(PagoProyecto $pago): RedirectResponse
    {
        $proyecto = $pago->proyecto;
        abort_unless($proyecto->user_id === Auth::id(), 403);

        DB::transaction(function () use ($pago) {
            $pago->movimiento?->delete();
            $pago->delete();
        });

        return back()->with('success', 'Pago eliminado.');
    }
}
