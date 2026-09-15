<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Cuenta;
use App\Models\Movimiento;
use Illuminate\Validation\ValidationException;

trait ValidaSaldoCuenta
{
    /**
     * Evita que un gasto/abono/transferencia deje la cuenta en negativo.
     * $movimientoActual: si se está editando un movimiento existente, se ignora su efecto actual al calcular el disponible.
     */
    protected function asegurarFondos(Cuenta $cuenta, float $monto, ?Movimiento $movimientoActual = null, string $campo = 'monto'): void
    {
        $disponible = $movimientoActual ? $cuenta->saldoSinMovimiento($movimientoActual) : $cuenta->saldo;

        if ($monto > $disponible) {
            throw ValidationException::withMessages([
                $campo => "La cuenta \"{$cuenta->nombre}\" no tiene saldo suficiente (disponible: \$".number_format($disponible, 2).').',
            ]);
        }
    }
}
