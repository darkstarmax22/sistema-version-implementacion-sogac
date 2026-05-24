<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class IntranetDataExporter
{
    /**
     * Replica todas las tablas configuradas desde intranet hacia simulación.
     *
     * @return array<string, int|string> tabla => filas exportadas o mensaje de error
     */
    public function exportAll(): array
    {
        if (! $this->canReachIntranet()) {
            return ['_error' => 'intranet_no_disponible'];
        }

        $results = [];
        foreach (config('intranet_sync.tables', []) as $table) {
            $results[$table] = $this->exportTable($table);
        }

        return $results;
    }

    /**
     * Exporta filas del usuario y tablas de referencia relacionadas.
     */
    public function exportUserContext(string $cedula): array
    {
        $cedula = trim($cedula);
        if ($cedula === '') {
            return [];
        }

        if (! $this->canReachIntranet()) {
            return ['_error' => 'intranet_no_disponible'];
        }

        $results = [];

        foreach (config('intranet_sync.user_scoped', []) as $table => $column) {
            $results[$table] = $this->exportTableRows($table, $column, $cedula);
        }

        // Programa del estudiante (si aplica)
        $est = DB::connection('intranet')
            ->table('estudiante')
            ->whereRaw('TRIM(est_cedula) = ?', [$cedula])
            ->first();

        if ($est && isset($est->est_cod_programa)) {
            $results['programa'] = $this->exportTableRows('programa', 'pro_codigo', $est->est_cod_programa);
        }

        return $results;
    }

    public function exportTable(string $table): int|string
    {
        if (! $this->canReachIntranet()) {
            return 'intranet_no_disponible';
        }

        try {
            if (! Schema::connection('intranet')->hasTable($table)) {
                return 'tabla_no_existe_intranet';
            }

            if (! Schema::connection('simulacion')->hasTable($table)) {
                Log::warning("Tabla {$table} no existe en simulación; omitiendo exportación completa.");

                return 'tabla_no_existe_simulacion';
            }

            $rows = DB::connection('intranet')->table($table)->get();
            DB::connection('simulacion')->table($table)->truncate();

            $count = 0;
            foreach ($rows->chunk(200) as $chunk) {
                $payload = $chunk->map(fn ($row) => (array) $row)->all();
                if ($payload !== []) {
                    DB::connection('simulacion')->table($table)->insert($payload);
                    $count += count($payload);
                }
            }

            return $count;
        } catch (\Throwable $e) {
            Log::warning("Exportación intranet→simulación fallida en {$table}: " . $e->getMessage());

            return 'error: ' . $e->getMessage();
        }
    }

    protected function exportTableRows(string $table, string $column, mixed $value): int|string
    {
        if (! $this->canReachIntranet()) {
            return 'intranet_no_disponible';
        }

        try {
            if (! Schema::connection('intranet')->hasTable($table)) {
                return 0;
            }

            if (! Schema::connection('simulacion')->hasTable($table)) {
                Log::warning("Tabla {$table} no existe en simulación; omitiendo exportación parcial.");

                return 'tabla_no_existe_simulacion';
            }

            $rows = DB::connection('intranet')
                ->table($table)
                ->whereRaw('TRIM(' . $column . ') = ?', [trim((string) $value)])
                ->get();

            DB::connection('simulacion')
                ->table($table)
                ->whereRaw('TRIM(' . $column . ') = ?', [trim((string) $value)])
                ->delete();

            $count = 0;
            foreach ($rows as $row) {
                DB::connection('simulacion')->table($table)->insert((array) $row);
                $count++;
            }

            return $count;
        } catch (\Throwable $e) {
            Log::warning("Exportación parcial {$table}.{$column}: " . $e->getMessage());

            return 'error: ' . $e->getMessage();
        }
    }

    protected function canReachIntranet(): bool
    {
        try {
            DB::connection('intranet')->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
