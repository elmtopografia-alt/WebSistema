<?php
// ARQUIVO: limpeza_demo.php
// FUNÇÃO: Apaga usuários e dados do banco DEMO que já venceram (passaram de 5 dias)
// COMO USAR: Acesse via navegador ou configure no Cron Job do cPanel

// Conecta no Banco DEMO
$conn = new mysqli('proposta.mysql.dbaas.com.br', 'proposta', 'Qtamaqmde5202@', 'proposta');

if ($conn->connect_error) { die("Erro conexao demo: " . $conn->connect_error); }

echo "<h2>🧹 Iniciando Limpeza do Ambiente Demo...</h2>";

// 1. Identificar Usuários Vencidos
$hoje = date('Y-m-d H:i:s');
$sql = "SELECT id_usuario FROM Usuarios WHERE validade_acesso IS NOT NULL AND validade_acesso < '$hoje'";
$res = $conn->query($sql);

$ids_para_apagar = [];
while ($row = $res->fetch_assoc()) {
    $ids_para_apagar[] = $row['id_usuario'];
}

$qtd = count($ids_para_apagar);

if ($qtd > 0) {
    $lista_ids = implode(',', $ids_para_apagar); // Ex: 10,12,15

    // 2. Apagar Dados Relacionados (Ordem: Filhos -> Pai)
    
    // Apaga itens das propostas
    $conn->query("DELETE FROM Proposta_Salarios WHERE id_proposta IN (SELECT id_proposta FROM Propostas WHERE id_criador IN ($lista_ids))");
    $conn->query("DELETE FROM Proposta_Estadia WHERE id_proposta IN (SELECT id_proposta FROM Propostas WHERE id_criador IN ($lista_ids))");
    $conn->query("DELETE FROM Proposta_Consumos WHERE id_proposta IN (SELECT id_proposta FROM Propostas WHERE id_criador IN ($lista_ids))");
    $conn->query("DELETE FROM Proposta_Locacao WHERE id_proposta IN (SELECT id_proposta FROM Propostas WHERE id_criador IN ($lista_ids))");
    $conn->query("DELETE FROM Proposta_Custos_Administrativos WHERE id_proposta IN (SELECT id_proposta FROM Propostas WHERE id_criador IN ($lista_ids))");
    
    // Apaga as Propostas
    $conn->query("DELETE FROM Propostas WHERE id_criador IN ($lista_ids)");

    // Apaga Clientes criados por eles
    $conn->query("DELETE FROM Clientes WHERE id_criador IN ($lista_ids)");

    // Apaga Dados da Empresa deles
    $conn->query("DELETE FROM DadosEmpresa WHERE id_criador IN ($lista_ids)");

    // 3. Finalmente, apaga os Usuários
    $conn->query("DELETE FROM Usuarios WHERE id_usuario IN ($lista_ids)");

    echo "<p style='color:green'>✅ Sucesso! <strong>$qtd usuários</strong> vencidos e todos os seus dados foram removidos.</p>";
} else {
    echo "<p style='color:blue'>ℹ️ Nenhum usuário vencido para apagar hoje.</p>";
}

$conn->close();
?>