<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogos del módulo en MySQL repositorio (no duplican tablas académicas de intranet).
 */
return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'mysql');

        if (! Schema::connection($connection)->hasTable('coordinaciones')) {
            Schema::connection($connection)->create('coordinaciones', function (Blueprint $table) {
                $table->id('coord_codigo');
                $table->string('coord_nombre', 255);
                $table->text('coord_descripcion')->nullable();
                $table->boolean('coord_activo')->default(true);
                $table->boolean('coord_alertar_comunidades')->default(false);
                $table->timestamps();
            });
        }

        if (Schema::connection($connection)->hasTable('linea_investigacions')
            && ! Schema::connection($connection)->hasColumn('linea_investigacions', 'coord_codigo')) {
            Schema::connection($connection)->table('linea_investigacions', function (Blueprint $table) {
                $table->unsignedBigInteger('coord_codigo')->nullable()->after('lin_area_de_investigacion');
            });
        }

        if (! Schema::connection($connection)->hasTable('componentes')) {
            Schema::connection($connection)->create('componentes', function (Blueprint $table) {
                $table->id('comp_codigo');
                $table->string('comp_nombre', 255);
                $table->unsignedBigInteger('coord_codigo')->nullable();
                $table->string('comp_anio', 32)->nullable();
                $table->boolean('comp_es_obligatorio')->default(true);
                $table->boolean('comp_estado_logico')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'mysql');

        if (Schema::connection($connection)->hasTable('linea_investigacions')
            && Schema::connection($connection)->hasColumn('linea_investigacions', 'coord_codigo')) {
            Schema::connection($connection)->table('linea_investigacions', function (Blueprint $table) {
                $table->dropColumn('coord_codigo');
            });
        }

        Schema::connection($connection)->dropIfExists('componentes');
        Schema::connection($connection)->dropIfExists('coordinaciones');
    }
};
