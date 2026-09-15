<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidaSaldoCuenta;
use App\Models\Cuenta;
use App\Models\Transferencia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferenciaController extends Controller
{
    use ValidaSaldoCuenta;

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'cuenta_origen_id' => 'required|exists:cuentas,id|different:cuenta_destino_id',
            'cuenta_destino_id' => 'required|exists:cuentas,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $this->asegurarFondos(Cuenta::findOrFail($datos['cuenta_origen_id']), (float) $datos['monto']);

        $datos['user_id'] = Auth::id();

        Transferencia::create($datos);

        return back()->with('success', 'Transferencia registrada.');
    }

    public function destroy(Transferencia $transferencia): RedirectResponse
    {
        abort_unless($transferencia->user_id === Auth::id(), 403);
        $transferencia->delete();

        return back()->with('success', 'Transferencia eliminada.');
    }
}
