<?php
//painel_financeiro.php
require_once '../config.php';

$competencia = date('Y-m');

// MRR
$mrr = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(valor_previsto) AS total FROM Ciclos_Financeiros WHERE competencia = '$competencia'"
))['total'] ?? 0;

// Recebido
$recebido = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(valor_pago) AS total FROM Pagamentos
     WHERE DATE_FORMAT(data_pagamento, '%Y-%m') = '$competencia'"
))['total'] ?? 0;

// Inadimplentes
$inadimplentes = mysqli_query(
    $conn,
    "SELECT * FROM Ciclos_Financeiros WHERE status = 'em_atraso'"
);
