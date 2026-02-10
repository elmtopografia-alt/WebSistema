<?php
// migration_crm_v3.php - Criação das tabelas do CRM 3.0 (Kimi Integration)

require_once 'db.php';

// Habilita exibição de erros para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Iniciando Migração CRM 3.0...</h1>";

$conn = Database::getProd();

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// 1. Tabela Historico_Interacoes
$sqlHistorico = "
CREATE TABLE IF NOT EXISTS Historico_Interacoes (
    id_historico INT AUTO_INCREMENT PRIMARY KEY,
    id_proposta INT NOT NULL,
    id_cliente INT NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'status_change, tarefa_criada, tarefa_concluida, email_enviado, whatsapp_enviado, nota_adicionada',
    conteudo TEXT NOT NULL,
    metadata JSON NULL,
    canal VARCHAR(20) NULL COMMENT 'sistema, email, whatsapp, ligacao',
    id_usuario INT NOT NULL,
    data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_proposta_data (id_proposta, data_interacao),
    INDEX idx_cliente_data (id_cliente, data_interacao),
    INDEX idx_usuario_data (id_usuario, data_interacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sqlHistorico) === TRUE) {
    echo "<p style='color:green'>✅ Tabela <strong>Historico_Interacoes</strong> verificada/criada.</p>";
} else {
    echo "<p style='color:red'>❌ Erro ao criar Historico_Interacoes: " . $conn->error . "</p>";
}

// 2. Tabela Tarefas_CRM (Inferida do código da API)
$sqlTarefas = "
CREATE TABLE IF NOT EXISTS Tarefas_CRM (
    id_tarefa INT AUTO_INCREMENT PRIMARY KEY,
    id_proposta INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'ligacao, email, whatsapp, reuniao, enviar_proposta, cobranca',
    descricao TEXT NOT NULL,
    data_agendada DATETIME NOT NULL,
    prioridade VARCHAR(20) DEFAULT 'media' COMMENT 'alta, media, baixa',
    status VARCHAR(20) DEFAULT 'pendente' COMMENT 'pendente, concluida, atrasada, cancelada',
    resultado VARCHAR(50) NULL COMMENT 'concluida, nao_atendeu, agendado_nova_data, recusou',
    observacao TEXT NULL,
    data_conclusao DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_usuario_status (id_usuario, status),
    INDEX idx_data_agendada (data_agendada),
    INDEX idx_proposta (id_proposta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sqlTarefas) === TRUE) {
    echo "<p style='color:green'>✅ Tabela <strong>Tarefas_CRM</strong> verificada/criada.</p>";
} else {
    echo "<p style='color:red'>❌ Erro ao criar Tarefas_CRM: " . $conn->error . "</p>";
}

echo "<h2>Migração Concluída. Pode fechar esta janela.</h2>";
?>
