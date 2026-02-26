<?php
header('Content-Type: text/plain');
echo "DIAGNOSTICO DE AMBIENTE\n";
echo "======================\n";
echo "PHP Version: " . phpversion() . "\n";
echo "CWD: " . getcwd() . "\n";
echo "File: " . __FILE__ . "\n";

echo "\nCONSTANTES:\n";
echo "ENVIRONMENT: " . (defined('ENVIRONMENT') ? ENVIRONMENT : 'UNDEFINED') . "\n";
echo "DB_PROD_HOST: " . (defined('DB_PROD_HOST') ? 'DEFINED' : 'UNDEFINED') . "\n";

echo "\nARQUIVOS INCLUIDOS:\n";
print_r(get_included_files());

echo "\nCONTEUDO DE db.php (Primeiras 50 linhas):\n";
if (file_exists('db.php')) {
    $lines = file('db.php');
    for ($i = 0; $i < min(50, count($lines)); $i++) {
        echo ($i + 1) . ": " . $lines[$i];
    }
} else {
    echo "db.php não encontrado no diretório atual.\n";
}

echo "\n\nCONTEUDO DE config.php (Primeiras 50 linhas):\n";
if (file_exists('config.php')) {
    $lines = file('config.php');
    for ($i = 0; $i < min(50, count($lines)); $i++) {
        echo ($i + 1) . ": " . $lines[$i];
    }
} else {
    echo "config.php não encontrado no diretório atual.\n";
}
