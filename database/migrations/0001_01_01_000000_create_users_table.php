<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // `users` ya existe cuando la app se conecta a un respaldo/instancia
        // real de la BD legacy (incluye columnas propias como `role_id`, que
        // esta app ya no usa — el RBAC nuevo vive en las tablas de Spatie).
        // Solo se crea en un entorno nuevo (ej. sqlite local sin datos reales).
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('username')->unique();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');

        // No se dropea `users` aquí a propósito: en un entorno conectado a
        // datos reales (legacy o RDS), esta migración nunca la creó, y un
        // `migrate:rollback` no debe poder borrar la tabla de usuarios real.
    }
};
