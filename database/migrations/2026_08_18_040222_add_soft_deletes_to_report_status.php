<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración puntual de la sección 4.5 del análisis técnico: report_status es
 * en la práctica la bitácora de auditoría del sistema — perder ese historial
 * ante un borrado accidental es un riesgo de negocio real, no cosmético.
 * Aditiva y de bajo riesgo: solo agrega la columna, no toca datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_status', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('report_status', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
