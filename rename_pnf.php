<?php

$baseDir = "c:\\xampp\\htdocs\\sistema";
$dirsToScan = [
    $baseDir . "\\app",
    $baseDir . "\\resources",
    $baseDir . "\\database",
    $baseDir . "\\routes"
];

function fileReplacements($content) {
    // Hide role names
    $content = str_replace("coordinador pnf", "COORDINADOR_PNF_TEMP_PLACEHOLDER", $content);
    $content = str_replace("Coordinador PNF", "COORDINADOR_PNF_TITLE_TEMP_PLACEHOLDER", $content);
    $content = str_replace("Coordinación de PNF", "COORDINACION_DE_PNF_TITLE_TEMP_PLACEHOLDER", $content);
    
    // RegEx replacements for exact boundaries
    $content = preg_replace('/\bPnfs\b/', 'Coordinaciones', $content);
    $content = preg_replace('/\bpnfs\b/', 'coordinaciones', $content);
    $content = preg_replace('/\bPnf\b/', 'Coordinacion', $content);
    $content = preg_replace('/\bpnf_id\b/', 'coordinacion_id', $content);

    // For plain 'pnf', skip it if it's right before a ] or '] or ' ]
    $content = preg_replace('/\bpnf\b(?!\s*\])(?!\s*\'\])(?!\'\s*\])/', 'coordinacion', $content);

    // Let's do string replaces for leftovers
    $content = str_replace("pnf_id", "coordinacion_id", $content);
    $content = str_replace("pnfs", "coordinaciones", $content);
    $content = str_replace("Pnfs", "Coordinaciones", $content);
    $content = str_replace("PNFs", "Coordinaciones", $content);
    $content = str_replace("Pnf", "Coordinacion", $content);
    $content = str_replace("PNF", "Coordinación", $content);
    $content = str_replace("pnf", "coordinacion", $content);

    // Restore role names
    $content = str_replace("COORDINADOR_PNF_TEMP_PLACEHOLDER", "coordinador pnf", $content);
    $content = str_replace("COORDINADOR_PNF_TITLE_TEMP_PLACEHOLDER", "Coordinador PNF", $content);
    $content = str_replace("COORDINACION_DE_PNF_TITLE_TEMP_PLACEHOLDER", "Coordinación", $content);

    $content = str_replace("filterCoordinacion", "filterCoordinacion", $content); // In case of capitalization
    $content = str_replace("CoordinacionManager", "CoordinacionManager", $content);

    return $content;
}

foreach ($dirsToScan as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            if (preg_match('/(\.php|\.blade\.php|\.js|\.css|\.json)$/', $file->getFilename())) {
                $filePath = $file->getPathname();
                $content = file_get_contents($filePath);
                $newContent = fileReplacements($content);
                if ($content !== $newContent) {
                    file_put_contents($filePath, $newContent);
                    echo "Updated content in " . $filePath . "\n";
                }
            }
        }
    }
}

echo "Content replacement finished. Starting file renaming...\n";

// Rename files and directories. Since renaming affects paths, we do it carefully.
$renameQueue = [];
foreach ($dirsToScan as $dir) {
    // Array to store paths because RecursiveIteratorIterator might get confused by renaming during loop
    $paths = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $fileinfo) {
        $paths[] = [$fileinfo->getPathname(), $fileinfo->getFilename(), $fileinfo->isDir()];
    }

    foreach ($paths as $pathData) {
        $oldPath = $pathData[0];
        $filename = $pathData[1];
        
        if (stripos($filename, 'pnf') !== false) {
            $newName = $filename;
            if (strpos($filename, 'Pnf.php') !== false) {
                $newName = str_replace('Pnf', 'Coordinacion', $filename);
            } elseif (strpos($filename, 'pnfs') !== false) {
                $newName = str_replace('pnfs', 'coordinaciones', $filename);
            } elseif (strpos($filename, 'pnf') !== false) {
                $newName = str_replace('pnf', 'coordinacion', $filename);
            }
            
            if ($newName !== $filename) {
                $newPath = dirname($oldPath) . DIRECTORY_SEPARATOR . $newName;
                echo "Renaming $oldPath to $newPath\n";
                rename($oldPath, $newPath);
            }
        }
    }
}

echo "Refactoring complete.\n";
