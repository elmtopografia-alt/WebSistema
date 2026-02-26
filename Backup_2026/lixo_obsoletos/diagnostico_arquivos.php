<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== LISTANDO ARQUIVOS GER* ===\n\n";

$files = glob(__DIR__ . '/ger*.php');
foreach ($files as $f) {
    echo basename($f) . "\n";
}
?>
