<?php

namespace App\Console\Commands;

use App\Services\IntranetDataExporter;
use Illuminate\Console\Command;

class SyncIntranetToSimulation extends Command
{
    protected $signature = 'app:sync-intranet-to-simulation';

    protected $description = 'Exporta las tablas configuradas de la BD intranet hacia la BD de simulación.';

    public function handle(IntranetDataExporter $exporter): int
    {
        $this->info('Sincronizando intranet → simulación...');

        $results = $exporter->exportAll();

        if (isset($results['_error'])) {
            $this->error('No se pudo conectar a la base de datos intranet.');

            return 1;
        }

        foreach ($results as $table => $count) {
            $this->line("  {$table}: {$count}");
        }

        $this->info('Sincronización finalizada.');

        return 0;
    }
}
