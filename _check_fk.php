<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=repositorio', 'root', '');

echo "=== FK referencing coordinaciones ===\n";
$stmt = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'coordinaciones' AND TABLE_SCHEMA = 'repositorio'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} (constraint: {$row['CONSTRAINT_NAME']})\n";
}

echo "=== Columns with 'coordinacion' in name ===\n";
$stmt2 = $pdo->query("SELECT COLUMN_NAME, TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME LIKE '%coordinacion%' AND TABLE_SCHEMA = 'repositorio'");
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']}\n";
}
