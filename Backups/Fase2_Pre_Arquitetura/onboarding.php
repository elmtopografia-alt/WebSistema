<?php
// Arquivo: onboarding.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = intval($_SESSION['id_usuario']);

/*
  Verifica se já existe assinatura ativa
*/
$sql = "
    SELECT id_assinatura
    FROM Assinaturas
    WHERE id_usuario = ?
      AND status = 'ativa'
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header('Location: financeiro_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Início do Sistema</title>
    <link rel="stylesheet" href="onboarding.css">
</head>
<body>

<div class="container">

    <h1>Bem-vindo ao Sistema</h1>

    <p>
        Antes de começar, precisamos concluir a configuração inicial.
        Isso leva menos de 1 minuto.
    </p>

    <form method="post" action="onboarding_processa.php">

        <label>Plano</label>
        <select name="plano" required>
            <option value="basico">básico</option>
            <option value="profissional">profissional</option>
        </select>

        <label>Valor mensal (R$)</label>
        <input type="number" name="valor_mensal" step="0.01" required>

        <button type="submit">Concluir configuração</button>
    </form>

</div>

</body>
</html>
