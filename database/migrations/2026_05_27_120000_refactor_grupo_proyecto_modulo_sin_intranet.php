<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla solo repositorio: sin columnas lapso/sección/programa (IDs de intranet).
 * Contexto académico en grp_contexto (JSON); integrantes en grp_miembros.
 */
return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'mysql');

        if (! Schema::connection($connection)->hasTable('grupo_proyecto_modulo')) {
            Schema::connection($connection)->create('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->id('grp_codigo');
                $table->string('grp_nombre', 120);
                $table->json('grp_contexto')->nullable();
                $table->unsignedBigInteger('grp_com_codigo')->nullable();
                $table->string('grp_creador_cedula', 20)->nullable()->index();
                $table->json('grp_miembros');
                $table->timestamps();
            });

            return;
        }

        $schema = Schema::connection($connection);

        if ($schema->hasColumn('grupo_proyecto_modulo', 'gpb_codigo') && ! $schema->hasColumn('grupo_proyecto_modulo', 'grp_codigo')) {
            $schema->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->renameColumn('gpb_codigo', 'grp_codigo');
            });
        }

        if ($schema->hasColumn('grupo_proyecto_modulo', 'gpb_nombre') && ! $schema->hasColumn('grupo_proyecto_modulo', 'grp_nombre')) {
            $schema->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->renameColumn('gpb_nombre', 'grp_nombre');
            });
        }

        if ($schema->hasColumn('grupo_proyecto_modulo', 'gpb_miembros') && ! $schema->hasColumn('grupo_proyecto_modulo', 'grp_miembros')) {
            $schema->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->renameColumn('gpb_miembros', 'grp_miembros');
            });
        }

        if ($schema->hasColumn('grupo_proyecto_modulo', 'gpb_estado')) {
            $schema->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->dropColumn('gpb_estado');
            });
        }

        foreach (['grp_lap_codigo', 'grp_sec_codigo', 'grp_pro_codigo'] as $col) {
            if ($schema->hasColumn('grupo_proyecto_modulo', $col)) {
                $this->migrarContextoDesdeColumnasIntranet($connection);
                $schema->table('grupo_proyecto_modulo', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        if (! $schema->hasColumn('grupo_proyecto_modulo', 'grp_contexto')) {
            $schema->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->json('grp_contexto')->nullable()->after('grp_nombre');
            });
        }

        if (! $schema->hasColumn('grupo_proyecto_modulo', 'grp_creador_cedula')) {
            $schema->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->string('grp_creador_cedula', 20)->nullable()->index()->after('grp_contexto');
            });
        }

        if (! $schema->hasColumn('grupo_proyecto_modulo', 'grp_com_codigo')) {
            $schema->table('grupo_proyecto_modulo', function (Blueprint $table) {
                $table->unsignedBigInteger('grp_com_codigo')->nullable()->after('grp_creador_cedula');
            });
        }
    }

    protected function migrarContextoDesdeColumnasIntranet(string $connection): void
    {
        $rows = DB::connection($connection)->table('grupo_proyecto_modulo')->get();
        foreach ($rows as $row) {
            if (! empty($row->grp_contexto)) {
                continue;
            }
            $ctx = array_filter([
                'lap_codigo' => $row->grp_lap_codigo ?? null,
                'sec_codigo' => $row->grp_sec_codigo ?? null,
                'pro_codigo' => $row->grp_pro_codigo ?? null,
            ], fn ($v) => $v !== null && $v !== '');
            if ($ctx !== []) {
                DB::connection($connection)->table('grupo_proyecto_modulo')
                    ->where('grp_codigo', $row->grp_codigo)
                    ->update(['grp_contexto' => json_encode($ctx, JSON_UNESCAPED_UNICODE)]);
            }
        }
    }

    public function down(): void
    {
        // Sin reversión automática: evitar reintroducir columnas académicas.
    }
};
