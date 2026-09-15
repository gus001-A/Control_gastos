<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProyectoController extends Controller
{
    public function index(): Response
    {
        $proyectos = Auth::user()->proyectos()->orderBy('estado')->orderByDesc('fecha_inicio')->get();

        return Inertia::render('Proyectos/Index', [
            'proyectos' => $proyectos,
            'estados' => Proyecto::ESTADOS,
        ]);
    }

    public function show(Proyecto $proyecto): Response
    {
        $this->autorizar($proyecto);

        $pagos = $proyecto->pagos()->with('cuenta:id,nombre')->orderByDesc('fecha')->orderByDesc('id')->get();

        return Inertia::render('Proyectos/Show', [
            'proyecto' => $proyecto,
            'pagos' => $pagos,
            'estados' => Proyecto::ESTADOS,
            'cuentas' => Auth::user()->cuentas()->where('activa', true)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);
        $datos['user_id'] = Auth::id();
        Proyecto::create($datos);

        return back()->with('success', 'Proyecto creado.');
    }

    public function update(Request $request, Proyecto $proyecto): RedirectResponse
    {
        $this->autorizar($proyecto);
        $datos = $this->validarDatos($request);
        if ($request->filled('estado')) {
            $datos['estado'] = $request->string('estado');
        }
        $proyecto->update($datos);

        return back()->with('success', 'Proyecto actualizado.');
    }

    public function destroy(Proyecto $proyecto): RedirectResponse
    {
        $this->autorizar($proyecto);
        $proyecto->delete();

        return redirect()->route('proyectos.index')->with('success', 'Proyecto eliminado.');
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'cliente' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'monto_cobrado' => 'required|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
            'notas' => 'nullable|string',
        ]);
    }

    private function autorizar(Proyecto $proyecto): void
    {
        abort_unless($proyecto->user_id === Auth::id(), 403);
    }
}
