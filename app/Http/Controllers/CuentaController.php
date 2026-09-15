<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CuentaController extends Controller
{
    public function index(): Response
    {
        $cuentas = Auth::user()->cuentas()->orderByDesc('activa')->orderBy('nombre')->get();

        return Inertia::render('Cuentas/Index', [
            'cuentas' => $cuentas,
            'tipos' => Cuenta::TIPOS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);
        Auth::user()->cuentas()->create($datos);

        return back()->with('success', 'Cuenta creada.');
    }

    public function update(Request $request, Cuenta $cuenta): RedirectResponse
    {
        $this->autorizar($cuenta);
        $datos = $this->validarDatos($request);
        $cuenta->update($datos);

        return back()->with('success', 'Cuenta actualizada.');
    }

    public function destroy(Cuenta $cuenta): RedirectResponse
    {
        $this->autorizar($cuenta);

        if ($cuenta->tieneMovimientos()) {
            $cuenta->update(['activa' => false]);

            return back()->with('success', 'La cuenta tiene movimientos, así que se marcó como inactiva.');
        }

        $cuenta->delete();

        return back()->with('success', 'Cuenta eliminada.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|in:'.implode(',', array_keys(Cuenta::TIPOS)),
            'saldo_inicial' => 'required|numeric',
            'color' => 'nullable|string|max:20',
            'notas' => 'nullable|string',
        ]);
    }

    private function autorizar(Cuenta $cuenta): void
    {
        abort_unless($cuenta->user_id === Auth::id(), 403);
    }
}
