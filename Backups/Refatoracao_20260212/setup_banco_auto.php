<?php
// setup_banco_auto.php
// Script de Automação: Popula o banco de dados se estiver vazio.

header('Content-Type: text/plain; charset=utf-8');
require_once 'vendor/autoload.php';
require_once 'db.php'; // Conexão Oficial

try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        $conn = Database::getProd();
    }

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }

    echo "=== Verificador de Banco de Dados ===\n\n";

    // 1. Verificar se a tabela está vazia
    $result = $conn->query("SELECT COUNT(*) as total FROM proposal_block_templates");
    if (!$result) {
        // Tenta criar a tabela se não existir
        echo "Tabela não encontrada. Tentando criar...\n";
        $sqlCreate = "CREATE TABLE IF NOT EXISTS `proposal_block_templates` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
          `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
          `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
          `default_content_json` text COLLATE utf8mb4_unicode_ci,
          `is_active` tinyint(1) DEFAULT '1',
          PRIMARY KEY (`id`),
          UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        if ($conn->query($sqlCreate)) {
            echo "Tabela criada com sucesso!\n";
            $count = 0;
        } else {
            die("Erro ao criar tabela: " . $conn->error);
        }
    } else {
        $row = $result->fetch_assoc();
        $count = (int)$row['total'];
    }

    echo "Blocos atuais no banco: $count\n";

    if ($count > 0) {
        echo "✅ O banco já tem dados. Nenhuma ação necessária.\n";
    } else {
        echo "⚠️ Banco vazio. Iniciando carga automática...\n";

        // SQLs de Insert (Gerados anteriormente)
        $inserts = [
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('cover', 'Capa Personalizada', 'presentation', '{\"is_required\":true,\"level\":\"root\",\"allowed_vars\":[\"client_name\",\"project_name\",\"date\"]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('executive_summary', 'Resumo Executivo', 'presentation', '{\"is_required\":true,\"level\":\"section\",\"allowed_vars\":[\"problem_summary\",\"solution_highlight\"]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('technical_scope', 'Escopo Técnico Detalhado', 'technical', '{\"is_required\":true,\"level\":\"section\",\"allowed_vars\":[\"services_list\",\"methodology_steps\"]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('field_work', 'Levantamento de Campo', 'technical', '{\"is_required\":true,\"level\":\"detail\",\"allowed_vars\":[]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('office_work', 'Processamento em Escritório', 'technical', '{\"is_required\":true,\"level\":\"detail\",\"allowed_vars\":[]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('deliverables', 'Entregáveis Finais', 'technical', '{\"is_required\":true,\"level\":\"detail\",\"allowed_vars\":[]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('methodology', 'Metodologia e Qualidade', 'technical', '{\"is_required\":false,\"level\":\"section\",\"allowed_vars\":[\"quality_standards\"]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('equipment', 'Equipamentos de Alta Precisão', 'technical', '{\"is_required\":true,\"level\":\"detail\",\"allowed_vars\":[]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('team', 'Equipe Técnica', 'technical', '{\"is_required\":true,\"level\":\"detail\",\"allowed_vars\":[]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('investment', 'Investimento e Condições', 'financial', '{\"is_required\":true,\"level\":\"section\",\"allowed_vars\":[\"total_value\",\"payment_conditions\"]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('cronograma_fisico_financeiro', 'Cronograma de Desembolso', 'financial', '{\"is_required\":true,\"level\":\"detail\",\"allowed_vars\":[]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('banking_data', 'Dados Bancários', 'financial', '{\"is_required\":true,\"level\":\"detail\",\"allowed_vars\":[]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('legal_terms', 'Termos e Validade', 'legal', '{\"is_required\":true,\"level\":\"section\",\"allowed_vars\":[\"validity_days\"]}', 1);",
            "INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active) VALUES ('acceptance', 'Aceite Digital', 'legal', '{\"is_required\":true,\"level\":\"root\",\"allowed_vars\":[\"client_signature\"]}', 1);"
        ];

        foreach ($inserts as $sql) {
            if ($conn->query($sql)) {
                echo ".";
            } else {
                echo "\n[ERRO] " . $conn->error . "\n";
            }
        }
        echo "\n\n✅ Carga Completa! Tabela populada com sucesso.";
    }
} catch (Exception $e) {
    echo "Erro Fatal: " . $e->getMessage();
}
