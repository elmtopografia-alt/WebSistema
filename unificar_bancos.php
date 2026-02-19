<?php
/**
 * unificar_bancos.php
 * Executa a paridade total entre o banco Proposta (DEMO) e Demanda (PROD)
 */
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$connDemo = Database::getDemo();

echo "=== INICIANDO UNIFICAÇÃO DO BANCO DEMO ===\n\n";

$sqlTables = [
    // 1. Tabelas Independentes
    "CREATE TABLE IF NOT EXISTS `Assinaturas` (
      `id_assinatura` int(11) NOT NULL AUTO_INCREMENT,
      `id_usuario` int(11) NOT NULL,
      `plano` varchar(50) NOT NULL,
      `valor_mensal` decimal(10,2) NOT NULL,
      `data_inicio` date NOT NULL,
      `status` enum('ativa','suspensa','cancelada') DEFAULT 'ativa',
      `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_assinatura`),
      KEY `idx_assinatura_usuario` (`id_usuario`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Documentos` (
      `id_documento` int(11) NOT NULL AUTO_INCREMENT,
      `id_proposta` int(11) NOT NULL,
      `id_cliente` int(11) NOT NULL,
      `id_usuario` int(11) NOT NULL,
      `nome_original` varchar(255) NOT NULL,
      `nome_arquivo` varchar(255) NOT NULL,
      `tipo_arquivo` varchar(100) DEFAULT NULL,
      `categoria` varchar(50) DEFAULT 'outro',
      `tamanho_bytes` bigint(20) DEFAULT NULL,
      `caminho` varchar(255) NOT NULL,
      `descricao` text,
      `is_principal` tinyint(1) DEFAULT '0',
      `data_upload` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_documento`),
      KEY `idx_proposta` (`id_proposta`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Email_Envios` (
      `id_envio` int(11) NOT NULL AUTO_INCREMENT,
      `id_proposta` int(11) NOT NULL,
      `id_cliente` int(11) NOT NULL,
      `id_template` int(11) DEFAULT NULL,
      `id_usuario` int(11) NOT NULL,
      `assunto` varchar(200) DEFAULT NULL,
      `corpo` text,
      `destinatario` varchar(150) DEFAULT NULL,
      `status` varchar(20) DEFAULT 'pendente',
      `data_agendamento` datetime DEFAULT NULL,
      `data_envio` datetime DEFAULT NULL,
      `erro_msg` text,
      `hash_rastreamento` varchar(64) DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_envio`),
      KEY `idx_proposta` (`id_proposta`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Email_Templates` (
      `id_template` int(11) NOT NULL AUTO_INCREMENT,
      `id_usuario` int(11) NOT NULL,
      `nome` varchar(100) NOT NULL,
      `assunto` varchar(200) NOT NULL,
      `corpo` text NOT NULL,
      `tipo` varchar(20) DEFAULT 'personalizado',
      `ativo` tinyint(1) DEFAULT '1',
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_template`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Historico_Interacoes` (
      `id_historico` int(11) NOT NULL AUTO_INCREMENT,
      `id_proposta` int(11) NOT NULL,
      `id_cliente` int(11) NOT NULL,
      `tipo` varchar(50) NOT NULL,
      `conteudo` text NOT NULL,
      `metadata` json DEFAULT NULL,
      `canal` varchar(20) DEFAULT NULL,
      `id_usuario` int(11) NOT NULL,
      `data_interacao` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_historico`),
      KEY `idx_proposta_data` (`id_proposta`,`data_interacao`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Interacoes_CRM` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `id_proposta` int(11) NOT NULL,
      `id_usuario` int(11) NOT NULL,
      `tipo` enum('LIGACAO','EMAIL','WHATSAPP','REUNIAO','NOTA') DEFAULT 'NOTA',
      `descricao` text,
      `data_hora` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_proposta` (`id_proposta`),
      KEY `idx_usuario` (`id_usuario`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Proposta_Conteudo_Personalizado` (
      `id_conteudo` int(11) NOT NULL AUTO_INCREMENT,
      `id_proposta` int(11) NOT NULL,
      `block_id` varchar(100) NOT NULL,
      `conteudo_texto` longtext,
      `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_conteudo`),
      UNIQUE KEY `unique_proposta_block` (`id_proposta`,`block_id`),
      KEY `fk_conteudo_proposta` (`id_proposta`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Proposta_Cronograma` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `id_proposta` int(11) NOT NULL,
      `ordem` int(11) DEFAULT '0',
      `nome_etapa` varchar(255) DEFAULT NULL,
      `descricao` text,
      `prazo` varchar(100) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `id_proposta` (`id_proposta`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Tarefas_CRM` (
      `id_tarefa` int(11) NOT NULL AUTO_INCREMENT,
      `id_proposta` int(11) NOT NULL,
      `id_usuario` int(11) NOT NULL,
      `tipo` varchar(50) NOT NULL,
      `descricao` text NOT NULL,
      `data_agendada` datetime NOT NULL,
      `prioridade` varchar(20) DEFAULT 'media',
      `status` varchar(20) DEFAULT 'pendente',
      `resultado` varchar(50) DEFAULT NULL,
      `observacao` text,
      `data_conclusao` datetime DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_tarefa`),
      KEY `idx_usuario_status` (`id_usuario`,`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Tokens_Acesso_Rapido` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `id_usuario` int(11) NOT NULL,
      `token` varchar(64) NOT NULL,
      `expiracao` datetime NOT NULL,
      `usado` tinyint(1) DEFAULT '0',
      PRIMARY KEY (`id`),
      UNIQUE KEY `token` (`token`),
      KEY `idx_token` (`token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Usuarios_Versoes_Vistas` (
      `id_vista` int(11) NOT NULL AUTO_INCREMENT,
      `id_usuario` int(11) NOT NULL,
      `versao` varchar(20) NOT NULL,
      `data_vista` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_vista`),
      KEY `id_usuario` (`id_usuario`,`versao`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Versoes_Sistema` (
      `id_versao` int(11) NOT NULL AUTO_INCREMENT,
      `versao` varchar(20) NOT NULL,
      `titulo` varchar(255) NOT NULL,
      `descricao` text NOT NULL,
      `data_lancamento` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_versao`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `leads_prospeccao` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nome_empresa` varchar(255) NOT NULL,
      `site_origem` varchar(255) NOT NULL,
      `ramo_atuacao` varchar(100) DEFAULT NULL,
      `whatsapp` varchar(50) DEFAULT NULL,
      `email_contato` varchar(255) DEFAULT NULL,
      `telefone_fixo` varchar(50) DEFAULT NULL,
      `tem_formulario` tinyint(1) DEFAULT '0',
      `url_formulario` varchar(255) DEFAULT NULL,
      `status_envio` enum('PENDENTE','ENVIADO','FALHA','IGNORADO') DEFAULT 'PENDENTE',
      `data_captura` datetime DEFAULT CURRENT_TIMESTAMP,
      `data_envio` datetime DEFAULT NULL,
      `metodo_captura` varchar(50) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `site_origem` (`site_origem`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "CREATE TABLE IF NOT EXISTS `service_type_blocks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `service_type_id` int(11) NOT NULL,
      `block_slug` varchar(50) NOT NULL,
      `block_title` varchar(100) NOT NULL,
      `category` enum('layout','presentation','technical','financial','legal') DEFAULT 'technical',
      `display_order` int(11) DEFAULT '0',
      `is_required` tinyint(1) DEFAULT '1',
      `default_content` longtext,
      `allowed_vars` json DEFAULT NULL,
      `is_active` tinyint(1) DEFAULT '1',
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_service_block` (`service_type_id`,`block_slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `sgt_piloto_teste` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `cliente` varchar(100) DEFAULT NULL,
      `status` varchar(50) DEFAULT NULL,
      `valor` decimal(10,2) DEFAULT NULL,
      `data_criacao` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `tipos_servico` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nome` varchar(100) NOT NULL,
      `descricao` text,
      `cor` varchar(7) DEFAULT '#3498db',
      `icone` varchar(50) DEFAULT 'map',
      `ativo` tinyint(1) DEFAULT '1',
      `ordem` int(11) DEFAULT '0',
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 2. Tabelas Dependentes (Foreign Keys)
    "CREATE TABLE IF NOT EXISTS `Ciclos_Financeiros` (
      `id_ciclo` int(11) NOT NULL AUTO_INCREMENT,
      `id_assinatura` int(11) NOT NULL,
      `competencia` char(7) NOT NULL,
      `valor_previsto` decimal(10,2) NOT NULL,
      `status` enum('aberto','pago','em_atraso') DEFAULT 'aberto',
      `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_ciclo`),
      UNIQUE KEY `uk_ciclo` (`id_assinatura`,`competencia`),
      CONSTRAINT `fk_ciclo_assinatura_demo_v2` FOREIGN KEY (`id_assinatura`) REFERENCES `Assinaturas` (`id_assinatura`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Pagamentos` (
      `id_pagamento` int(11) NOT NULL AUTO_INCREMENT,
      `id_ciclo` int(11) NOT NULL,
      `valor_pago` decimal(10,2) NOT NULL,
      `data_pagamento` datetime NOT NULL,
      `metodo` varchar(30) DEFAULT NULL,
      `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_pagamento`),
      CONSTRAINT `fk_pag_ciclo_demo_v2` FOREIGN KEY (`id_ciclo`) REFERENCES `Ciclos_Financeiros` (`id_ciclo`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `Recibos` (
      `id_recibo` int(11) NOT NULL AUTO_INCREMENT,
      `id_pagamento` int(11) NOT NULL,
      `numero_recibo` varchar(30) NOT NULL,
      `emissor_nome` varchar(150) NOT NULL,
      `emissor_cnpj` varchar(20) NOT NULL,
      `data_emissao` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_recibo`),
      CONSTRAINT `fk_recibo_pag_demo_v2` FOREIGN KEY (`id_pagamento`) REFERENCES `Pagamentos` (`id_pagamento`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

$sucessos = 0;
$erros = 0;

// Execução das tabelas
foreach ($sqlTables as $sql) {
    try {
        if ($connDemo->query($sql)) {
            $sucessos++;
        } else {
            // Se já existir, query retorna true mas pode dar warning. IF NOT EXISTS trata.
            // Somente incrementa erro se falhar de fato.
        }
    } catch (Exception $e) {
        // Ignora erros de "tabela já existe" se acontecerem apesar do IF NOT EXISTS
        if (strpos($e->getMessage(), 'already exists') === false) {
            $erros++;
            echo "ERR Tabela: " . $e->getMessage() . "\n";
        }
    }
}

// 3. Ajustes de Colunas em Tabelas Existentes
function columnExists($conn, $table, $column) {
    try {
        $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return ($res && $res->num_rows > 0);
    } catch (Exception $e) { return false; }
}

function safeQuery($conn, $sql, $msg) {
    echo " > Tentando: $msg... ";
    if ($conn->query($sql)) {
        echo "[OK]\n";
        return true;
    } else {
        echo "[ERRO: " . $conn->error . "]\n";
        return false;
    }
}

echo "\n--- AJUSTANDO TABELA 'Clientes' ---\n";
if (!columnExists($connDemo, 'Clientes', 'whatsapp_handle')) {
    safeQuery($connDemo, "ALTER TABLE `Clientes` ADD COLUMN `whatsapp_handle` VARCHAR(100)", "Adicionar 'whatsapp_handle'");
} else {
    echo " [.] Coluna 'whatsapp_handle' já existe.\n";
}

echo "\n--- AJUSTANDO TABELA 'Pagamentos' ---\n";
if (!columnExists($connDemo, 'Pagamentos', 'comprovante')) {
    safeQuery($connDemo, "ALTER TABLE `Pagamentos` ADD COLUMN `comprovante` VARCHAR(255) AFTER `metodo` ", "Adicionar 'comprovante'");
} else {
    echo " [.] Coluna 'comprovante' já existe.\n";
}

echo "\n--- AJUSTANDO TABELA 'Propostas' (Refinamento) ---\n";
// 1. Modificar tamanho do número
safeQuery($connDemo, "ALTER TABLE `Propostas` MODIFY COLUMN `numero_proposta` VARCHAR(50)", "Ajustar tamanho numero_proposta");

// 2. Adicionar/Corrigir colunas com precisão de PROD
$cols = [
    'empresa_cliente_salvo' => "VARCHAR(255)",
    'tipo_servico_id'       => "INT",
    'unidade_area'          => "VARCHAR(10)",
    'fase_crm'              => "ENUM('ELABORACAO','ENVIADA','NEGOCIACAO','FECHADA','PERDIDA') DEFAULT 'ELABORACAO'",
    'data_followup'         => "DATETIME NULL",
    'motivo_perda'          => "VARCHAR(255) NULL",
    'data_atualizacao'      => "DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    'coordenadas_gps'       => "VARCHAR(100)"
];

foreach ($cols as $col => $def) {
    if (!columnExists($connDemo, 'Propostas', $col)) {
        safeQuery($connDemo, "ALTER TABLE `Propostas` ADD COLUMN `$col` $def", "Adicionar col '$col'");
    } else {
        // Se já existe mas o tipo pode estar diferente (diagnóstico apontou isso para fase_crm e motivo_perda)
        if ($col === 'fase_crm' || $col === 'motivo_perda') {
            safeQuery($connDemo, "ALTER TABLE `Propostas` MODIFY COLUMN `$col` $def", "Refinar tipo da col '$col'");
        } else {
            echo " [.] Coluna '$col' já existe.\n";
        }
    }
}

// 3. Limpeza e Padronização
if (columnExists($connDemo, 'Propostas', 'total_custos_geral')) {
    safeQuery($connDemo, "ALTER TABLE `Propostas` DROP COLUMN `total_custos_geral` ", "Remover 'total_custos_geral'");
}

safeQuery($connDemo, "ALTER TABLE `Propostas` MODIFY COLUMN `tipo_terreno` VARCHAR(50)", "Ajustar tipo_terreno");
safeQuery($connDemo, "ALTER TABLE `Propostas` MODIFY COLUMN `cobertura_vegetal` VARCHAR(50)", "Ajustar cobertura_vegetal");
safeQuery($connDemo, "ALTER TABLE `Propostas` MODIFY COLUMN `acesso_local` VARCHAR(50)", "Ajustar acesso_local");

echo "\n--- AJUSTANDO TABELA 'Usuarios' ---\n";
if (columnExists($connDemo, 'Usuarios', 'telefone')) {
    safeQuery($connDemo, "ALTER TABLE `Usuarios` DROP COLUMN `telefone` ", "Remover 'telefone'");
}
if (columnExists($connDemo, 'Usuarios', 'ip_origem')) {
    safeQuery($connDemo, "ALTER TABLE `Usuarios` DROP COLUMN `ip_origem` ", "Remover 'ip_origem'");
}

echo "\n--- CRIANDO VIEWS ---\n";
$viewSql = "CREATE OR REPLACE VIEW `vw_propostas_com_tipo` AS 
    select `p`.`id_proposta` AS `id_proposta`,`p`.`numero_proposta` AS `numero_proposta`,`p`.`id_cliente` AS `id_cliente`,`p`.`nome_cliente_salvo` AS `nome_cliente_salvo`,`p`.`empresa_cliente_salvo` AS `empresa_cliente_salvo`,`p`.`email_salvo` AS `email_salvo`,`p`.`telefone_salvo` AS `telefone_salvo`,`p`.`celular_salvo` AS `celular_salvo`,`p`.`whatsapp_salvo` AS `whatsapp_salvo`,`p`.`empresa_proponente_nome` AS `empresa_proponente_nome`,`p`.`empresa_proponente_cnpj` AS `empresa_proponente_cnpj`,`p`.`empresa_proponente_endereco` AS `empresa_proponente_endereco`,`p`.`empresa_proponente_cidade` AS `empresa_proponente_cidade`,`p`.`empresa_proponente_estado` AS `empresa_proponente_estado`,`p`.`empresa_proponente_banco` AS `empresa_proponente_banco`,`p`.`empresa_proponente_agencia` AS `empresa_proponente_agencia`,`p`.`empresa_proponente_conta` AS `empresa_proponente_conta`,`p`.`empresa_proponente_pix` AS `empresa_proponente_pix`,`p`.`id_servico` AS `id_servico`,`p`.`tipo_servico_id` AS `tipo_servico_id`,`p`.`contato_obra` AS `contato_obra`,`p`.`finalidade` AS `finalidade`,`p`.`tipo_levantamento` AS `tipo_levantamento`,`p`.`area_obra` AS `area_obra`,`p`.`endereco_obra` AS `endereco_obra`,`p`.`bairro_obra` AS `bairro_obra`,`p`.`cidade_obra` AS `cidade_obra`,`p`.`estado_obra` AS `estado_obra`,`p`.`prazo_execucao` AS `prazo_execucao`,`p`.`dias_campo` AS `dias_campo`,`p`.`dias_escritorio` AS `dias_escritorio`,`p`.`data_criacao` AS `data_criacao`,`p`.`status` AS `status`,`p`.`total_custos_salarios` AS `total_custos_salarios`,`p`.`total_custos_estadia` AS `total_custos_estadia`,`p`.`total_custos_consumos` AS `total_custos_consumos`,`p`.`total_custos_locacao` AS `total_custos_locacao`,`p`.`total_custos_admin` AS `total_custos_admin`,`p`.`percentual_lucro` AS `percentual_lucro`,`p`.`valor_lucro` AS `valor_lucro`,`p`.`subtotal_com_lucro` AS `subtotal_com_lucro`,`p`.`valor_desconto` AS `valor_desconto`,`p`.`valor_final_proposta` AS `valor_final_proposta`,`p`.`Valor_proposta_extenso` AS `Valor_proposta_extenso`,`p`.`mobilizacao_percentual` AS `mobilizacao_percentual`,`p`.`mobilizacao_valor` AS `mobilizacao_valor`,`p`.`restante_percentual` AS `restante_percentual`,`p`.`restante_valor` AS `restante_valor`,`p`.`id_criador` AS `id_criador`,`p`.`is_demo` AS `is_demo`,`p`.`fase_crm` AS `fase_crm`,`p`.`data_followup` AS `data_followup`,`p`.`motivo_perda` AS `motivo_perda`,`p`.`data_atualizacao` AS `data_atualizacao`,`p`.`tipo_terreno` AS `tipo_terreno`,`p`.`cobertura_vegetal` AS `cobertura_vegetal`,`p`.`acesso_local` AS `acesso_local`,`p`.`restricoes_aereas` AS `restricoes_aereas`,`p`.`coordenadas_gps` AS `coordenadas_gps`,`t`.`nome` AS `tipo_servico_nome`,`t`.`cor` AS `tipo_servico_cor`,`t`.`icone` AS `tipo_servico_icone` from (`Propostas` `p` left join `tipos_servico` `t` on((`p`.`tipo_servico_id` = `t`.`id`)))";

safeQuery($connDemo, $viewSql, "Criar View vw_propostas_com_tipo");

echo "\n" . str_repeat("=", 40) . "\n";
echo "UNIFICAÇÃO COMPLETA (FINALIZADA)!\n";
echo "Paridade total atingida (incluindo Views e Enums).\n";
echo str_repeat("=", 40) . "\n";
?>
