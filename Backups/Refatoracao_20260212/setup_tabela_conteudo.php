<?php
// setup_tabela_conteudo.php
// Cria a tabela de FRAGMENTOS DE CONTEÚDO (O "Elo" que o usuário pediu)

require_once 'vendor/autoload.php';
require_once 'db.php';

try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        $conn = Database::getProd();
    }

    echo "=== Criando Tabela de Fragmentos (Elos) ===\n\n";

    $sql = "CREATE TABLE IF NOT EXISTS `proposal_content_variations` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `block_slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL, -- O Elo com o Bloco Pai
      `variation_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL, -- Ex: 'Planialtimétrico', 'Drone'
      `content_text` LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL, -- O Texto Puro
      `is_default` tinyint(1) DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `block_slug` (`block_slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if ($conn->query($sql)) {
        echo "✅ Tabela 'proposal_content_variations' criada!\n";
    } else {
        die("❌ Erro: " . $conn->error);
    }

    // Inserindo o Texto Modelo que o usuário mandou (Fragmentado)

    // 1. Apresentação
    $txtApresentacao = "A \${Empresa} é referência em serviços topográficos de alta precisão. Com vasto acervo técnico...";
    $conn->query("INSERT INTO proposal_content_variations (block_slug, variation_name, content_text, is_default) VALUES ('executive_summary', 'Padrão Corporativo', '$txtApresentacao', 1)");

    // 2. Escopo (Planialtimétrico)
    $txtEscopo = "3. Escopo do Serviço\nLevantamento Planialtimétrico Cadastral\nO serviço consiste no mapeamento completo...\n\n3.1. Atividades de Campo\n● Implantação de Marcos...\n● Levantamento de Elementos Urbanos...\n\n3.2. Atividades de Escritório\n● Processamento de Dados...\n● Produção Gráfica...";

    // Escape safety
    $txtEscopo = $conn->real_escape_string($txtEscopo);

    $conn->query("INSERT INTO proposal_content_variations (block_slug, variation_name, content_text, is_default) VALUES ('technical_scope', 'Levantamento Planialtimétrico', '$txtEscopo', 1)");

    echo "✅ Fragmentos iniciais inseridos com os ELOS corretos (block_slug).\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
