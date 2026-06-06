<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('dual_database.repositorio_connection', 'mysql');

        if (Schema::connection($connection)->hasTable('profesor_proyecto_modulo')) {
            return;
        }

        Schema::connection($connection)->create('profesor_proyecto_modulo', function (Blueprint $table) {
            $table->id('ppm_codigo');
            $table->string('ppm_cedula', 20);
            $table->unsignedBigInteger('ppm_lap_codigo');
            $table->unsignedBigInteger('ppm_sud_codigo')->nullable();
            $table->string('ppm_anio')->nullable();
            $table->string('ppm_seccion')->nullable();
            $table->unsignedBigInteger('ppm_coordinacion_id')->nullable();
            $table->boolean('ppm_habilitado')->default(true);
            $table->timestamps();

            $table->unique(['ppm_cedula', 'ppm_lap_codigo']);
        });
    }

    public function down(): void
    {
        $connection = config('dual_database.repositorio_connection', 'mysql');
        Schema::connection($connection)->dropIfExists('profesor_proyecto_modulo');
    }
};
