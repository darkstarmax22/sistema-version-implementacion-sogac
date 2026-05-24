<?php

namespace App\Console\Commands;

use App\Services\AcademicCatalog;
use Illuminate\Console\Command;

class SyncAcademicMirrorsToRepositorio extends Command
{
    protected $signature = 'app:sync-academic-mirrors';

    protected $description = 'Copia tablas académicas de intranet a MySQL repositorio (doble lectura local).';

    public function handle(AcademicCatalog $catalog): int
    {
        $tables = config('dual_database.repositorio_mirror_tables', []);

        foreach ($tables as $table) {
            $count = $catalog->mirrorTableToRepositorio($table);
            $this->line("  {$table}: {$count} filas");
        }

        $this->info('Espejo académico en repositorio actualizado.');

        return 0;
    }
}
