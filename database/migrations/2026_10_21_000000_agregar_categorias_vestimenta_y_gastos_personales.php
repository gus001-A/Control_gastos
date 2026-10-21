<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NUEVAS = [
        ['nombre' => 'Vestimenta', 'color' => '#6366f1'],
        ['nombre' => 'Gastos personales', 'color' => '#14b8a6'],
    ];

    /**
     * Agrega "Vestimenta" y "Gastos personales" a los usuarios que ya existían
     * (los nuevos las reciben desde Categoria::DEFAULT).
     */
    public function up(): void
    {
        $userIds = DB::table('users')->pluck('id');

        foreach ($userIds as $userId) {
            foreach (self::NUEVAS as $categoria) {
                $existe = DB::table('categorias')
                    ->where('user_id', $userId)->where('nombre', $categoria['nombre'])->exists();

                if ($existe) {
                    continue;
                }

                DB::table('categorias')->insert([
                    'user_id' => $userId,
                    'nombre' => $categoria['nombre'],
                    'tipo' => 'gasto',
                    'color' => $categoria['color'],
                    'sistema' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Migración de datos: no se revierte para no perder movimientos ya categorizados.
    }
};
