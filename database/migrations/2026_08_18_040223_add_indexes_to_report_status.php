<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración puntual de la sección 4.5 del análisis técnico: report_status se
 * consulta constantemente por rango de fechas y por machinery_id/status_id
 * (Dashboard, Reportar) — identificado como cuello de botella de
 * escalabilidad. Aditiva y de bajo riesgo: solo agrega índices.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_status', function (Blueprint $table) {
            $table->index(['machinery_id', 'created_at']);
            $table->index(['status_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('report_status', function (Blueprint $table) {
            $table->dropIndex(['machinery_id', 'created_at']);
            $table->dropIndex(['status_id', 'created_at']);
        });
    }
};
