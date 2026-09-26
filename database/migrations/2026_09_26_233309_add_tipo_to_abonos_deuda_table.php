<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonos_deuda', function (Blueprint $table) {
            $table->string('tipo', 10)->default('abono')->after('deuda_id');
        });
    }

    public function down(): void
    {
        Schema::table('abonos_deuda', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
