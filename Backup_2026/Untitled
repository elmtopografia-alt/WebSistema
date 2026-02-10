<?php
// Inicio: mudar_status.php
// Função: Recebe a solicitação de alteração de status vinda do Painel e atualiza o Banco.
// Impacto: Ao mudar o status aqui, os gráficos (api_graficos.php) e KPIs são recalculados automaticamente.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Verificação de Segurança
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: painel.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$ambiente = $_SESSION['ambiente'] ?? 'producao';

// Conecta no banco correto
$conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

// 2. Coleta e Sanitização
$id_proposta = intval($_POST['id_proposta'] ?? 0);
$novo_status = trim($_POST['novo_status'] ?? '');

// Lista estrita de status permitidos (Deve bater com o que a API de gráficos lê)
$status_validos = [
    'Em elaboração', 
    'Enviada', 
    'Aprovada', 
    'Cancelada', 
    'Concluída' // Alias para Aprovada em alguns contextos
];

if ($id_proposta > 0 && in_array($novo_status, $status_validos)) {
    
    try {
        // 3. Atualização no Banco
        // Garante que só altera se a proposta pertencer ao usuário logado (id_criador)
        $sql = "UPDATE Propostas SET status = ? WHERE id_proposta = ? AND id_criador = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sii', $novo_status, $id_proposta, $id_usuario);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Sucesso real
                header("Location: painel.php?msg=status_atualizado");
            } else {
                // Comando rodou, mas nada mudou (talvez já estivesse com esse status ou ID errado)
                header("Location: painel.php?msg=sem_alteracao");
            }
        } else {
            throw new Exception("Falha na execução do SQL.");
        }

    } catch (Exception $e) {
        // Erro técnico
        header("Location: painel.php?msg=erro");
    }
    
} else {
    // Tentativa de injeção de status inválido
    header("Location: painel.php?msg=erro_dados_invalidos");
}
exit;
// Fim: mudar_status.php
?>