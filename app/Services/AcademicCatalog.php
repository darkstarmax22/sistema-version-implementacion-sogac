<?php

namespace App\Services;

use App\Helpers\DualDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicCatalog
{
    /**
     * Estudiantes desde intranet (persona + estudiante).
     */
    public function estudiantesForSelect(?string $search = null, int $limit = 50): Collection
    {
        $conn = DualDatabase::academicConnection();

        $query = DB::connection($conn)
            ->table('estudiante as e')
            ->join('persona as p', DB::raw('TRIM(e.est_cedula)'), '=', DB::raw('TRIM(p.per_cedula)'))
            ->select([
                DB::raw('TRIM(e.est_cedula) as cedula'),
                DB::raw("TRIM(COALESCE(p.per_nombres, '')) as nombres"),
                DB::raw("TRIM(COALESCE(p.per_apellidos, '')) as apellidos"),
            ])
            ->limit($limit);

        if ($search) {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('TRIM(e.est_cedula) LIKE ?', [$term])
                    ->orWhereRaw('TRIM(p.per_nombres) LIKE ?', [$term])
                    ->orWhereRaw('TRIM(p.per_apellidos) LIKE ?', [$term]);
            });
        }

        return collect($query->get())->map(function ($row) {
            $row->nombre_completo = trim($row->nombres . ' ' . $row->apellidos);

            return $row;
        });
    }

    /**
     * Copia tablas académicas de intranet/simulación hacia MySQL repositorio (respaldo local).
     */
    public function mirrorTableToRepositorio(string $table): int
    {
        $localTable = config("dual_database.local_aliases.{$table}", $table);
        $repo = DualDatabase::repositorioConnection();

        if (! Schema::connection($repo)->hasTable($localTable)) {
            return 0;
        }

        $source = DualDatabase::academicConnection();
        if (! Schema::connection($source)->hasTable($table)) {
            return 0;
        }

        $rows = DB::connection($source)->table($table)->get();
        DB::connection($repo)->table($localTable)->truncate();

        $count = 0;
        foreach ($rows->chunk(200) as $chunk) {
            $payload = $chunk->map(fn ($row) => (array) $row)->all();
            if ($payload !== []) {
                DB::connection($repo)->table($localTable)->insert($payload);
                $count += count($payload);
            }
        }

        return $count;
    }
}
