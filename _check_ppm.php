<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=repositorio', 'root', '');
    $stmt = $pdo->query("SHOW COLUMNS FROM profesor_proyecto_modulo");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
