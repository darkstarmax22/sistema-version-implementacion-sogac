<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = DB::connection('mysql')->getSchemaBuilder()->getColumnListing('profesor_proyecto_modulo');
echo 'mysql connection: ' . implode(', ', $cols) . PHP_EOL;

$pdo = new PDO('mysql:host=127.0.0.1;dbname=repositorio', 'root', '');
$stmt = $pdo->query("SHOW COLUMNS FROM profesor_proyecto_modulo");
$cols2 = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cols2[] = $row['Field'];
}
echo 'raw PDO: ' . implode(', ', $cols2) . PHP_EOL;
