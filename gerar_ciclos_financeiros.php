<?php
// Arquivo: gerar_ciclos_financeiros.php

require_once __DIR__ . '/../config/config.php';

$dataAtual = date('Y-m');
$dataHoje = date('Y-m-d');

$sql = "
    SELECT a.id_assinatura, a.valor_mensal
    FROM Assinaturas a
    WHERE a.status = 'ativa'
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($assinatura = $result->fetch_assoc()) {

        $id_assinatura = $assinatura['id_assinatura'];
        $valor = $assinatura['valor_mensal'];

        $verifica = $conn->prepare("
            SELECT id_ciclo 
            FROM Ciclos_Financeiros 
            WHERE id_assinatura = ? AND competencia = ?
        ");
        $verifica->bind_param("is", $id_assinatura, $dataAtual);
        $verifica->execute();
        $verifica->store_result();

        if ($verifica->num_rows == 0) {
            $insert = $conn->prepare("
                INSERT INTO Ciclos_Financeiros 
                (id_assinatura, competencia, valor_previsto, status)
                VALUES (?, ?, ?, 'aberto')
            ");
            $insert->bind_param("isd", $id_assinatura, $dataAtual, $valor);
            $insert->execute();
        }
    }
}

file_put_contents(
    __DIR__ . '/../logs/cron.log',
    date('Y-m-d H:i:s') . " - ciclos financeiros gerados\n",
    FILE_APPEND
);
