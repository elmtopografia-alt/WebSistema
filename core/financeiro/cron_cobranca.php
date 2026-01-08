<?php
/**
 * cron_cobranca.php
 * Automação - Gera cobranças mensais.
 * Regra: Executado via CRON. Não tem HTML.
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/financeiro_calculadora.php';

// Apenas CLI ou proteção por token
if (php_sapi_name() !== 'cli' && !isset($_GET['token_seguranca'])) {
    die('Acesso negado');
}

$conn = Database::getProd(); // Cron roda sempre em produção (ou parametrizado)

echo "Iniciando rotina de cobranca...\n";

// 1. Busca assinaturas ativas que vencem hoje ou geram fatura hoje
$sql = "SELECT * FROM Assinaturas WHERE status = 'ativa'";
$result = $conn->query($sql);

while ($assinatura = $result->fetch_assoc()) {
    // Lógica simplificada: Gera fatura para o próximo mês se não existir
    $proximoVencimento = date('Y-m-d', strtotime('+1 month')); // Exemplo
    
    // Verifica se já existe ciclo
    $check = $conn->prepare("SELECT id_ciclo FROM Ciclos_Financeiros WHERE id_assinatura = ? AND competencia = ?");
    $competencia = date('m/Y', strtotime('+1 month'));
    $check->bind_param("is", $assinatura['id_assinatura'], $competencia);
    $check->execute();
    
    if ($check->get_result()->num_rows == 0) {
        // Calcula valor
        $valor = FinanceiroCalculadora::calcularMensalidade($assinatura['valor_mensal'], $assinatura['periodicidade']);
        
        // Cria ciclo
        $insert = $conn->prepare("INSERT INTO Ciclos_Financeiros (id_assinatura, competencia, data_vencimento, valor_previsto, status) VALUES (?, ?, ?, ?, 'pendente')");
        $insert->bind_param("issd", $assinatura['id_assinatura'], $competencia, $proximoVencimento, $valor);
        $insert->execute();
        
        echo "Fatura gerada para Assinatura ID {$assinatura['id_assinatura']}\n";
    }
}

echo "Concluido.\n";
?>
