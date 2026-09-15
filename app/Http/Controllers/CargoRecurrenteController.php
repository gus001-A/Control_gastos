<?php

namespace App\Http\Controllers;

use App\Models\CargoRecurrente;
use App\Models\Movimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CargoRecurrenteController extends Controller
{
    public function index(): Response
    {
        $cargos = Auth::user()->cargosRecurrentes()
            ->with(['cuenta:id,nombre', 'categoria:id,nombre,color'])
            ->orderByDesc('activo')
            ->orderBy('proxima_fecha')
            ->get();

        return Inertia::render('CargosRecurrentes/Index', [
            'cargos' => $cargos,
            'frecuencias' => CargoRecurrente::FRECUENCIAS,
            'cuentas' => Auth::user()->cuentas()->where('activa', true)->orderBy('nombre')->get(['id', 'nombre']),
            'categorias' => Auth::user()->categorias()->where('tipo', 'gasto')->orderBy('nombre')->get(['id', 'nombre', 'color']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);
        $datos['user_id'] = Auth::id();
        CargoRecurrente::create($datos);

        return back()->with('success', 'Cargo recurrente creado.');
    }

    public function update(Request $request, CargoRecurrente $recurrente): RedirectResponse
    {
        $this->autorizar($recurrente);
        $recurrente->update($this->validarDatos($request));

        return back()->with('success', 'Cargo recurrente actualizado.');
    }

    public function destroy(CargoRecurrente $recurrente): RedirectResponse
    {
        $this->autorizar($recurrente);
        $recurrente->delete();

        return back()->with('success', 'Cargo recurrente eliminado.');
    }

    public function pagar(Request $request, CargoRecurrente $recurrente): RedirectResponse
    {
        $this->autorizar($recurrente);

        $datos = $request->validate([
            'fecha' => 'required|date',
        ]);

        DB::transaction(function () use ($recurrente, $datos) {
            Movimiento::create([
                'user_id' => Auth::id(),
                'cuenta_id' => $recurrente->cuenta_id,
                'categoria_id' => $recurrente->categoria_id,
                'tipo' => 'gasto',
                'monto' => $recurrente->monto,
                'descripcion' => $recurrente->nombre,
                'fecha' => $datos['fecha'],
                'cargo_recurrente_id' => $recurrente->id,
                'origen' => 'cargo_recurrente',
            ]);

            $siguiente = $recurrente->siguienteFechaDesde($recurrente->proxima_fecha);
            $actualiza = ['proxima_fecha' => $siguiente];

            if ($recurrente->fecha_fin && $siguiente->gt($recurrente->fecha_fin)) {
                $actualiza['activo'] = false;
            }

            $recurrente->update($actualiza);
        });

        return back()->with('success', 'Pago registrado y siguiente fecha actualizada.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'cuenta_id' => 'required|exists:cuentas,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'monto' => 'required|numeric|min:0.01',
            'frecuencia' => 'required|in:'.implode(',', array_keys(CargoRecurrente::FRECUENCIAS)),
            'proxima_fecha' => 'required|date',
            'fecha_fin' => 'nullable|date',
            'activo' => 'boolean',
            'notas' => 'nullable|string',
        ]);
    }

    private function autorizar(CargoRecurrente $recurrente): void
    {
        abort_unless($recurrente->user_id === Auth::id(), 403);
    }
}
