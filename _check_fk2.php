<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=repositorio', 'root', '');

echo "=== All tables ===\n";
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "  {$row[0]}\n";
}

echo "\n=== coordinaciones columns ===\n";
$stmt2 = $pdo->query("SHOW COLUMNS FROM coordinaciones");
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}

echo "\n=== FK constraints in repositorio ===\n";
$stmt3 = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE CONSTRAINT_NAME LIKE '%fk%' AND TABLE_SCHEMA = 'repositorio' AND REFERENCED_TABLE_NAME IS NOT NULL");
while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']} ({$row['CONSTRAINT_NAME']})\n";
}
