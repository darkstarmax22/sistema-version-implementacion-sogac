<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $migs = DB::connection('mysql')->table('migrations')->get();
    echo "Migrations in mysql:\n";
    foreach ($migs as $m) {
        echo "  [Batch {$m->batch}] {$m->migration}\n";
    }
} catch (Throwable $e) {
    echo "Error reading migrations: " . $e->getMessage() . "\n";
}

try {
    $tables = DB::connection('mysql')->select('SHOW TABLES');
    echo "\nTables in mysql:\n";
    foreach ($tables as $t) {
        echo "  " . current((array)$t) . "\n";
    }
} catch (Throwable $e) {
    echo "Error listing tables: " . $e->getMessage() . "\n";
}
