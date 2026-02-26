<?php
ob_start();
include __DIR__ . '/fetch_ids_temp.php';
$output = ob_get_clean();
file_put_contents(__DIR__ . '/db_ids_output.txt', $output);
echo "Output salvo em db_ids_output.txt";
?>
