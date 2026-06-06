<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=repositorio', 'root', '');
foreach (['linea_investigacions', 'componentes', 'proyectos', 'profesor_proyecto_modulo'] as $t) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM $t");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "=== $t ===\n";
        foreach ($cols as $c) {
            echo "  {$c['Field']} ({$c['Type']})\n";
        }
    } catch (Exception $e) {
        echo "=== $t === TABLE NOT FOUND\n";
    }
}
