<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MovimientoController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = Auth::id();

        $query = Movimiento::where('user_id', $userId)
            ->with(['cuenta:id,nombre,color', 'categoria:id,nombre,color', 'deuda:id,nombre', 'proyecto:id,nombre']);

        if ($request->filled('cuenta_id')) {
            $query->where('cuenta_id', $request->integer('cuenta_id'));
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->integer('categoria_id'));
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo'));
        }
        if ($request->filled('texto')) {
            $query->where('descripcion', 'like', '%'.$request->string('texto').'%');
        }

        $movimientos = $query->orderByDesc('fecha')->orderByDesc('id')->limit(500)->get();

        $transferenciasList = \App\Models\Transferencia::where('user_id', $userId)
            ->with(['cuentaOrigen:id,nombre', 'cuentaDestino:id,nombre'])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return Inertia::render('Movimientos/Index', [
            'movimientos' => $movimientos,
            'transferencias' => $transferenciasList,
            'cuentas' => Auth::user()->cuentas()->orderBy('nombre')->get(['id', 'nombre', 'activa', 'color']),
            'categorias' => Auth::user()->categorias()->orderBy('tipo')->orderBy('nombre')->get(),
            'filtros' => $request->only(['cuenta_id', 'categoria_id', 'tipo', 'texto']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);
        $datos['user_id'] = Auth::id();
        $datos['origen'] = 'manual';
        Movimiento::create($datos);

        return back()->with('success', 'Movimiento registrado.');
    }

    public function update(Request $request, Movimiento $movimiento): RedirectResponse
    {
        $this->autorizar($movimiento);

        if ($movimiento->origen !== 'manual') {
            return back()->with('error', 'Este movimiento se generó automáticamente; edítalo desde deudas o proyectos.');
        }

        $movimiento->update($this->validarDatos($request));

        return back()->with('success', 'Movimiento actualizado.');
    }

    public function destroy(Movimiento $movimiento): RedirectResponse
    {
        $this->autorizar($movimiento);

        if ($movimiento->origen !== 'manual') {
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
            'fecha' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
        ]);
    }

    private function autorizar(Movimiento $movimiento): void
    {
        abort_unless($movimiento->user_id === Auth::id(), 403);
    }
}
