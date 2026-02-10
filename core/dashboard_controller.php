<?php
/**
 * core/dashboard_controller.php
 * Controlador do Dashboard do Cliente
 * Responsável por: Validação de Sessão, KPIs e Verificações de Assinatura
 */

require_once __DIR__ . '/../session_validator.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// Verifica se é realmente um usuário PRO (produção)
$ambiente = $_SESSION['ambiente'] ?? 'producao';
if ($ambiente === 'demo') {
    header('Location: Cli_demo.php');
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiro_nome = explode(' ', trim($nome_usuario))[0];
$perfil = $_SESSION['perfil'] ?? 'cliente';

$kpi = [
    'total' => 0,
    'aprovadas' => 0,
    'valor_total' => 0
];
$assinaturaAtiva = false;
$diasRestantes = 0;
$validade = null;

// Carrega dados do usuário e KPIs
try {
    $conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();
    
    // 1. Busca validade da assinatura
    $stmt = $conn->prepare("SELECT validade_acesso FROM Usuarios WHERE id_usuario = ?");
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    $usuario = $res->fetch_assoc();
    
    $validade = $usuario['validade_acesso'] ? new DateTime($usuario['validade_acesso']) : null;
    $hoje = new DateTime();
    
    if ($validade) {
        $diasRestantes = $hoje->diff($validade)->days;
        // Se a data de hoje for menor que a validade
        $assinaturaAtiva = $hoje < $validade;
        
        // Se já passou, dias restantes é zero (não negativo visualmente)
        if ($hoje > $validade) {
            $diasRestantes = 0;
        }
    }
    
    // 2. KPIs rápidos (Do Mês Atual)
    $stmtKPI = $conn->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN LOWER(status) LIKE '%aprov%' THEN 1 ELSE 0 END) as aprovadas,
        SUM(valor_final_proposta) as valor_total
        FROM Propostas 
        WHERE id_criador = ? 
        AND MONTH(data_criacao) = MONTH(CURRENT_DATE()) 
        AND YEAR(data_criacao) = YEAR(CURRENT_DATE())");
        
    $stmtKPI->bind_param('i', $id_usuario);
    $stmtKPI->execute();
    $resultKPI = $stmtKPI->get_result()->fetch_assoc();
    
    if ($resultKPI) {
        $kpi = $resultKPI;
    }
    
} catch (Exception $e) {
    // Em produção, logar o erro e não exibir na tela
    error_log("Erro no Dashboard Controller: " . $e->getMessage());
}

return [
    'usuario' => [
        'id' => $id_usuario,
        'nome_completo' => $nome_usuario,
        'primeiro_nome' => $primeiro_nome,
        'perfil' => $perfil
    ],
    'assinatura' => [
        'ativa' => $assinaturaAtiva,
        'validade' => $validade,
        'dias_restantes' => $diasRestantes,
        'validade_formatada' => $validade ? $validade->format('d/m/Y') : '-'
    ],
    'kpi' => $kpi
];
