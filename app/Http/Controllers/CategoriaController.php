<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:ingreso,gasto',
            'color' => 'nullable|string|max:20',
        ]);
        $datos['user_id'] = Auth::id();
        $datos['sistema'] = false;

        Categoria::create($datos);

        return back()->with('success', 'Categoría creada.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        abort_unless($categoria->user_id === Auth::id(), 403);

        if ($categoria->sistema) {
            return back()->with('error', 'Esa categoría la usa el sistema y no se puede eliminar.');
        }

        $categoria->delete();

        return back()->with('success', 'Categoría eliminada.');
    }
}
