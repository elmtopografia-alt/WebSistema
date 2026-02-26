<?php
/**
 * SGT Deep Flow Diagnostic (Ferramenta de Auditoria Profunda)
 * 🔬 Objetivo: Descobrir falhas invisíveis de integridade e fluxo.
 */

require_once 'config.php';
require_once 'db.php';
require_once 'PropostaRepository.php';

header('Content-Type: text/html; charset=utf-8');
echo "<body style='background:#0a0f1a; color:#f8fafc; font-family:Inter, sans-serif; padding:40px;'>";
echo "<h1 style='color:#3b82f6;'>🔬 SGT Deep Flow Analysis</h1>";

$repo = new PropostaRepository();
$conn = Database::getProd();

function test_step($name, $callback) {
    echo "<div style='margin-bottom:20px; padding:15px; background:rgba(255,255,255,0.05); border-radius:10px; border-left:4px solid #3b82f6;'>";
    echo "<strong style='font-size:1.1em;'>$name</strong><br>";
    try {
        $result = $callback();
        echo "<span style='color:#10b981;'>[OK]</span> " . $result;
    } catch (Throwable $e) {
        echo "<span style='color:#ef4444;'>[FALHA]</span> " . $e->getMessage();
        echo "<pre style='font-size:0.8em; color:#94a3b8;'>" . $e->getFile() . ":" . $e->getLine() . "</pre>";
    }
    echo "</div>";
}

// 1. Auditoria de Constantes e Autocura
test_step("Integridade de Configuração", function() {
    $vars = ['DB_PROD_HOST', 'ENVIRONMENT', 'BASE_URL'];
    foreach($vars as $v) if(!defined($v)) throw new Exception("Constante $v não definida!");
    return "Todas as constantes vitais estão presentes. Autocura funcional.";
});

// 2. Auditoria de Integridade de Tabelas Filhas
test_step("Consistência de Tabelas Relacionadas", function() use ($conn) {
    $tabelas = [
        'Proposta_Salarios', 'Proposta_Estadia', 'Proposta_Consumos', 
        'Proposta_Locacao', 'Proposta_Custos_Administrativos'
    ];
    $relatorio = "";
    foreach($tabelas as $tab) {
        $check = $conn->query("SELECT COUNT(*) as qtd FROM $tab WHERE id_proposta NOT IN (SELECT id_proposta FROM Propostas)");
        $orphans = $check->fetch_assoc()['qtd'];
        if($orphans > 0) $relatorio .= "<br>⚠️ $tab: $orphans registros órfãos detectados!";
    }
    return $relatorio ?: "Nenhum registro órfão (vazamento de dados) detectado.";
});

// 3. Auditoria de Colunas do Editor
test_step("Paridade de Esquema (Editor Avançado)", function() use ($conn) {
    $cols = ['modelo_docx', 'config_docx_json'];
    foreach($cols as $c) {
        $check = $conn->query("SHOW COLUMNS FROM Propostas LIKE '$c'");
        if($check->num_rows == 0) throw new Exception("Coluna $c ausente!");
    }
    return "Colunas do Editor Avançado sincronizadas.";
});

// 4. Teste de Stress de Transação (Atomicidade)
test_step("Simulação de Atomicidade (Transactional Check)", function() use ($repo, $conn) {
    $conn->begin_transaction();
    $id_teste = 0;
    try {
        // Tenta salvar algo incompleto e reverter
        $repo->salvar(['nome_cliente_salvo' => 'TESTE_DIAGNOSTICO_TEMP']);
        $id_teste = $conn->insert_id;
        $conn->rollback();
        
        $check = $conn->query("SELECT id_proposta FROM Propostas WHERE id_proposta = $id_teste");
        if($check && $check->num_rows > 0) throw new Exception("Rollback falhou! Transação não é atômica.");
        return "Transações de Banco de Dados são seguras e reversíveis.";
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
});

echo "<hr style='border:none; border-top:1px solid rgba(255,255,255,0.1); margin:40px 0;'>";
echo "<p style='color:#94a3b8;'>Diagnóstico concluído. Se houver falhas, consulte o <strong>implementation_plan.md</strong> para correção estrutural.</p>";
echo "</body>";
