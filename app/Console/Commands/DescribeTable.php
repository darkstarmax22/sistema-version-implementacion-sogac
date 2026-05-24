<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DescribeTable extends Command
{
    protected $signature = 'app:describe-table {table=proyectos}';

    public function handle(): int
    {
        $table = $this->argument('table');
        $cols = DB::connection('mysql')->select("SHOW COLUMNS FROM `{$table}`");
        foreach ($cols as $col) {
            $this->line($col->Field . ' | ' . $col->Type);
        }

        return 0;
    }
}
