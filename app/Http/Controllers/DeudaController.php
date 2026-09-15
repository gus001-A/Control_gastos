<?php

namespace App\Http\Controllers;

use App\Models\Deuda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DeudaController extends Controller
{
    public function index(): Response
    {
        $deudas = Auth::user()->deudas()->withSum('abonos', 'monto')
            ->orderBy('estado')->orderByRaw('fecha_limite IS NULL')->orderBy('fecha_limite')->get();

        return Inertia::render('Deudas/Index', [
            'deudas' => $deudas,
        ]);
    }

    public function show(Deuda $deuda): Response
    {
        $this->autorizar($deuda);

        $abonos = $deuda->abonos()->with('cuenta:id,nombre')->orderByDesc('fecha')->orderByDesc('id')->get();

        return Inertia::render('Deudas/Show', [
            'deuda' => $deuda,
            'abonos' => $abonos,
            'cuentas' => Auth::user()->cuentas()->conSaldo()->where('activa', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);
        $datos['user_id'] = Auth::id();
        Deuda::create($datos);

        return back()->with('success', 'Deuda creada.');
    }

    public function update(Request $request, Deuda $deuda): RedirectResponse
    {
        $this->autorizar($deuda);
        $deuda->update($this->validarDatos($request));

        return back()->with('success', 'Deuda actualizada.');
    }

    public function destroy(Deuda $deuda): RedirectResponse
    {
        $this->autorizar($deuda);
        $deuda->delete();

        return redirect()->route('deudas.index')->with('success', 'Deuda eliminada.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'acreedor' => 'nullable|string|max:255',
            'monto_total' => 'required|numeric|min:0.01',
            'fecha_inicio' => 'nullable|date',
            'fecha_limite' => 'nullable|date',
            'notas' => 'nullable|string',
        ]);
    }

    private function autorizar(Deuda $deuda): void
    {
        abort_unless($deuda->user_id === Auth::id(), 403);
    }
}
