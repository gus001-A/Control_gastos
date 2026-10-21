<?php

namespace App\Support;

class Fechas
{
    public const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public const MESES_ABREV = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
    ];

    /** Fechas de registros que mueven dinero: no se permite registrar algo que aún no pasó. */
    public const REGLA_NO_FUTURA = 'required|date|before_or_equal:today';

    public const MENSAJES_NO_FUTURA = [
        'fecha.before_or_equal' => 'La fecha no puede ser posterior a hoy.',
    ];

    public static function nombreMes(int $mes): string
    {
        return self::MESES[$mes] ?? (string) $mes;
    }

    public static function mesAbrev(int $mes): string
    {
        return self::MESES_ABREV[$mes] ?? (string) $mes;
    }
}
