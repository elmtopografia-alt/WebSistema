<?php
// Arquivo: financeiro_dashboard.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = intval($_SESSION['id_usuario']);

/*
  1) Busca assinatura ativa do usuário
*/
$sql_assinatura = "
    SELECT 
        a.id_assinatura,
        a.plano,
        a.valor_mensal,
        a.status
    FROM Assinaturas a
    WHERE a.id_usuario = ?
    ORDER BY a.id_assinatura DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql_assinatura);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$assinatura = $stmt->get_result()->fetch_assoc();

if (!$assinatura) {
    die('Nenhuma assinatura encontrada.');
}

/*
  2) Busca ciclo financeiro atual (mais recente)
*/
$sql_ciclo = "
    SELECT 
        c.id_ciclo,
        c.competencia,
        c.valor_previsto,
        c.status
    FROM Ciclos_Financeiros c
    WHERE c.id_assinatura = ?
    ORDER BY c.competencia DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql_ciclo);
$stmt->bind_param("i", $assinatura['id_assinatura']);
$stmt->execute();
$ciclo = $stmt->get_result()->fetch_assoc();

/*
  3) Verifica pagamento (se houver)
*/
$pagamento = null;
if ($ciclo) {
    $sql_pagamento = "
        SELECT 
            data_pagamento,
            metodo
        FROM Pagamentos
        WHERE id_ciclo = ?
        ORDER BY data_pagamento DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql_pagamento);
    $stmt->bind_param("i", $ciclo['id_ciclo']);
    $stmt->execute();
    $pagamento = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Financeiro</title>
    <link rel="stylesheet" href="financeiro_dashboard.css">
</head>
<body>

<div class="container">

    <h1>Financeiro</h1>

    <section class="box">
        <h2>Plano Atual</h2>
        <p><strong>Plano:</strong> <?php echo htmlspecialchars($assinatura['plano']); ?></p>
        <p><strong>Valor mensal:</strong> R$ <?php echo number_format($assinatura['valor_mensal'], 2, ',', '.'); ?></p>
        <p><strong>Status da assinatura:</strong> <?php echo $assinatura['status']; ?></p>
    </section>

    <?php if ($ciclo): ?>
    <section class="box">
        <h2>Situação Atual</h2>

        <p><strong>Competência:</strong> <?php echo $ciclo['competencia']; ?></p>
        <p><strong>Valor:</strong> R$ <?php echo number_format($ciclo['valor_previsto'], 2, ',', '.'); ?></p>
        <p><strong>Status do ciclo:</strong> <?php echo $ciclo['status']; ?></p>

        <?php if ($ciclo['status'] === 'pago'): ?>
            <div class="status pago">
                Pagamento confirmado.
            </div>
            <?php if ($pagamento): ?>
                <p class="info">
                    Pago em <?php echo date('d/m/Y H:i', strtotime($pagamento['data_pagamento'])); ?>
                    via <?php echo htmlspecialchars($pagamento['metodo']); ?>
                </p>
            <?php endif; ?>

        <?php else: ?>
            <div class="status pendente">
                Pagamento pendente.
            </div>

            <a class="btn" href="registrar_pagamento.php?id_ciclo=<?php echo $ciclo['id_ciclo']; ?>">
                Registrar pagamento
            </a>
        <?php endif; ?>
    </section>
    <?php endif; ?>

</div>

</body>
</html>
