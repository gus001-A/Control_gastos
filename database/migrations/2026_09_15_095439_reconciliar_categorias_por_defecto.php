<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Quita la categoría "Educación" de cada usuario y reasigna sus movimientos
     * a "Otros gastos" en vez de dejarlos sin categoría.
     */
    public function up(): void
    {
        $userIds = DB::table('users')->pluck('id');

        foreach ($userIds as $userId) {
            $educacionId = DB::table('categorias')
                ->where('user_id', $userId)->where('nombre', 'Educación')->value('id');

            if (! $educacionId) {
                continue;
            }

            $otrosGastosId = DB::table('categorias')
                ->where('user_id', $userId)->where('nombre', 'Otros gastos')->value('id');

            if ($otrosGastosId) {
                DB::table('movimientos')->where('categoria_id', $educacionId)->update(['categoria_id' => $otrosGastosId]);
            }

            DB::table('categorias')->where('id', $educacionId)->delete();
        }
    }

    public function down(): void
    {
        // Migración de datos: no se revierte.
    }
};
