<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sistema de un solo usuario: en vez de pedir login, entra directamente con el
 * primer usuario registrado. Si la base está vacía, crea ese usuario.
 */
class IniciarSesionAutomatica
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.auto_login') && ! Auth::check()) {
            Auth::login($this->usuarioPrincipal(), remember: true);
        }

        return $next($request);
    }

    private function usuarioPrincipal(): User
    {
        $usuario = User::orderBy('id')->first();

        if ($usuario) {
            return $usuario;
        }

        $usuario = User::create([
            'name' => 'Mi cuenta',
            'email' => 'yo@control-gastos.local',
            'password' => Str::random(40),
        ]);
        $usuario->forceFill(['email_verified_at' => now()])->save();
        $usuario->seedCategoriasPorDefecto();

        return $usuario;
    }
}
