<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = 'mysql';

        if (! Schema::connection($connection)->hasTable('coordinaciones')) {
            Schema::connection($connection)->create('coordinaciones', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->boolean('alertar_comunidades')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::connection($connection)->hasTable('lapso_academico')) {
            Schema::connection($connection)->create('lapso_academico', function (Blueprint $table) {
                $table->string('lap_codigo', 32)->primary();
                $table->string('lap_nombre');
                $table->date('lap_fecha_inicio')->nullable();
                $table->date('lap_fecha_fin')->nullable();
                $table->string('lap_cod_tipo_lapso', 10)->nullable();
                $table->string('lap_cod_universidad', 10)->nullable();
                $table->string('lap_condicion', 5)->nullable();
                $table->char('lap_estatus', 1)->default('A');
                $table->char('lap_cerrado', 1)->nullable();
                $table->text('lap_nota')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('lapso_academico');
        Schema::connection('mysql')->dropIfExists('coordinaciones');
    }
};
