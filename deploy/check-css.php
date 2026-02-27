<?php
/**
 * Diagnóstico temporal: verifica el estado de main.css en el servidor.
 * ELIMINAR después de usar.
 */
header('Content-Type: text/plain; charset=utf-8');

$basePath = dirname(__DIR__);
$cssPath = $basePath . '/assets/css/main.css';

echo "=== CSS Diagnostic ===\n\n";
echo "Base path: " . $basePath . "\n";
echo "CSS path:  " . $cssPath . "\n";
echo "File exists: " . (file_exists($cssPath) ? 'YES' : 'NO') . "\n";

if (file_exists($cssPath)) {
    echo "File size:  " . filesize($cssPath) . " bytes\n";
    echo "Is symlink: " . (is_link($cssPath) ? 'YES -> ' . readlink($cssPath) : 'NO') . "\n";
    echo "Perms:      " . substr(sprintf('%o', fileperms($cssPath)), -4) . "\n";
    echo "Owner:      " . posix_getpwuid(fileowner($cssPath))['name'] . "\n";
    echo "Modified:   " . date('Y-m-d H:i:s', filemtime($cssPath)) . "\n";
    echo "\n--- First 5 lines ---\n";
    $f = fopen($cssPath, 'r');
    for ($i = 0; $i < 5 && !feof($f); $i++) {
        echo fgets($f);
    }
    fclose($f);
    echo "\n\n--- Line count ---\n";
    echo count(file($cssPath)) . " lines\n";
}

// Check the deploy repo too
$repoPath = '/home/purranque/regalospurranque-repo/assets/css/main.css';
echo "\n=== Repo CSS ===\n";
echo "Repo path: " . $repoPath . "\n";
echo "Exists:    " . (file_exists($repoPath) ? 'YES' : 'NO') . "\n";
if (file_exists($repoPath)) {
    echo "File size: " . filesize($repoPath) . " bytes\n";
    echo "\n--- First 3 lines ---\n";
    $f = fopen($repoPath, 'r');
    for ($i = 0; $i < 3 && !feof($f); $i++) {
        echo fgets($f);
    }
    fclose($f);
}
