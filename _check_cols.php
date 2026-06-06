<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $pdo = DB::connection('mysql')->getPdo();
    $tables = ['auditorias'];
    foreach ($tables as $table) {
        echo "=== $table ===\n";
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} ({$row['Type']}) [{$row['Key']}]\n";
        }
        echo "\n";
    }
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
