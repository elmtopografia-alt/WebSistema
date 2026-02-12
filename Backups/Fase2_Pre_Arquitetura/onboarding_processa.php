<?php
// Arquivo: onboarding_processa.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario   = intval($_SESSION['id_usuario']);
$plano        = strtolower(trim($_POST['plano']));
$valor_mensal = floatval($_POST['valor_mensal']);

if ($valor_mensal <= 0) {
    die('Valor inválido.');
}

$conn->begin_transaction();

try {

    /*
      1) Cria assinatura
    */
    $sql = "
        INSERT INTO Assinaturas
        (id_usuario, plano, valor_mensal, data_inicio, status)
        VALUES (?, ?, ?, CURDATE(), 'ativa')
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $id_usuario, $plano, $valor_mensal);
    $stmt->execute();

    $id_assinatura = $stmt->insert_id;

    /*
      2) Cria primeiro ciclo financeiro
    */
    $competencia = date('Y-m');

    $sql = "
        INSERT INTO Ciclos_Financeiros
        (id_assinatura, competencia, valor_previsto, status)
        VALUES (?, ?, ?, 'aberto')
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $id_assinatura, $competencia, $valor_mensal);
    $stmt->execute();

    $conn->commit();

    header('Location: financeiro_dashboard.php');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die('Erro ao concluir onboarding.');
}
