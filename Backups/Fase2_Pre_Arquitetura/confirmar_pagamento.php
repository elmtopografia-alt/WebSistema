<?php
//confirmar_pagamento.php
define('FINANCEIRO', true);
require_once '../config.php';
require_once 'funcoes_financeiras.php';

$idCiclo = intval($_POST['id_ciclo']);
$metodo  = mysqli_real_escape_string($conn, $_POST['metodo']);
$comprovante = $_POST['comprovante'] ?? null;

// 1. Buscar ciclo
$sql = "
SELECT id_ciclo, valor_previsto, status
FROM Ciclos_Financeiros
WHERE id_ciclo = $idCiclo
FOR UPDATE
";
$res = mysqli_query($conn, $sql);
$ciclo = mysqli_fetch_assoc($res);

if (!$ciclo || $ciclo['status'] === 'pago') {
    die('Ciclo inválido ou já pago');
}

// 2. Criar pagamento
$sqlPagamento = "
INSERT INTO Pagamentos (id_ciclo, valor_pago, data_pagamento, metodo, comprovante)
VALUES (
    {$ciclo['id_ciclo']},
    {$ciclo['valor_previsto']},
    NOW(),
    '$metodo',
    " . ($comprovante ? "'$comprovante'" : "NULL") . "
)";
mysqli_query($conn, $sqlPagamento);
$idPagamento = mysqli_insert_id($conn);

// 3. Gerar recibo
$emissor = obterEmissor($conn);
$numeroRecibo = gerarNumeroRecibo($idPagamento);

$sqlRecibo = "
INSERT INTO Recibos (id_pagamento, numero_recibo, emissor_nome, emissor_cnpj)
VALUES (
    $idPagamento,
    '$numeroRecibo',
    '{$emissor['Empresa']}',
    '{$emissor['CNPJ']}'
)";
mysqli_query($conn, $sqlRecibo);

// 4. Atualizar ciclo
mysqli_query(
    $conn,
    "UPDATE Ciclos_Financeiros SET status = 'pago' WHERE id_ciclo = $idCiclo"
);

echo 'Pagamento confirmado e recibo gerado com sucesso';
