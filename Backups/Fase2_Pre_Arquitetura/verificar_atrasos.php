<?php
// Arquivo: verificar_atrasos.php

require_once __DIR__ . '/../config/config.php';

$dataLimite = date('Y-m-01');

$sql = "
    UPDATE Ciclos_Financeiros
    SET status = 'em_atraso'
    WHERE status = 'aberto'
      AND competencia < DATE_FORMAT(?, '%Y-%m')
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dataLimite);
$stmt->execute();

file_put_contents(
    __DIR__ . '/../logs/cron.log',
    date('Y-m-d H:i:s') . " - ciclos em atraso verificados\n",
    FILE_APPEND
);
