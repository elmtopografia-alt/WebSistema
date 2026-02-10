<?php
// run_install.php
// INSTALADOR WEB - Executa o setup do banco via Navegador
// (Para evitar o problema de terminal)

header('Content-Type: text/html; charset=utf-8');
require_once 'vendor/autoload.php';
require_once 'db.php';

function installDatabase($conn, $name)
{
    if (!$conn) {
        echo "<p style='color:red'>❌ Falha na conexão com $name.</p>";
        return;
    }

    echo "<h3>🛠️ Configurando Banco: $name</h3>";

    // 1. Tabela de Templates (Blocos)
    $sql1 = "CREATE TABLE IF NOT EXISTS `proposal_block_templates` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
      `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
      `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
      `default_content_json` LONGTEXT COLLATE utf8mb4_unicode_ci,
      `is_active` tinyint(1) DEFAULT '1',
      PRIMARY KEY (`id`),
      UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if ($conn->query($sql1)) echo "<div style='color:green'>Query 1 OK</div>";
    else echo "<div style='color:red'>Erro 1: " . $conn->error . "</div>";

    // 2. Tabela de Conteúdo (Variations)
    $sql2 = "CREATE TABLE IF NOT EXISTS `proposal_content_variations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `block_slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
          `variation_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
          `content_text` LONGTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
          `is_default` tinyint(1) DEFAULT '0',
          PRIMARY KEY (`id`),
          KEY `block_slug` (`block_slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if ($conn->query($sql2)) echo "<div style='color:green'>Query 2 OK</div>";
    else echo "<div style='color:red'>Erro 2: " . $conn->error . "</div>";

    // 3. Upgrade para LONGTEXT (Garantia)
    $conn->query("ALTER TABLE `proposal_block_templates` MODIFY `default_content_json` LONGTEXT");
    $conn->query("ALTER TABLE `proposal_content_variations` MODIFY `content_text` LONGTEXT");

    // 4. Inserir Dados Básicos
    // -- Escopo
    $txtEscopo = "3. Escopo do Serviço\nLevantamento Planialtimétrico Cadastral\nO serviço consiste no mapeamento completo...\n\n3.1. Atividades de Campo\n● Implantação de Marcos...\n● Levantamento de Elementos Urbanos...\n\n3.2. Atividades de Escritório\n● Processamento de Dados...\n● Produção Gráfica...";
    $txtEscopo = $conn->real_escape_string($txtEscopo);

    $check = $conn->query("SELECT id FROM proposal_content_variations WHERE block_slug='technical_scope' LIMIT 1");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO proposal_content_variations (block_slug, variation_name, content_text, is_default) VALUES ('technical_scope', 'Levantamento Planialtimétrico', '$txtEscopo', 1)");
        echo "<div>➜ Dados inseridos: Escopo Planialtimétrico</div>";
    }

    echo "<hr>";
}

?>
<!DOCTYPE html>
<html>

<body style="font-family: sans-serif; padding: 20px; line-height: 1.5;">
    <h1>Instalador Automático</h1>
    <p>Corrigindo tabelas e inserindo dados...</p>

    <?php
    installDatabase(Database::getProd(), "PRODUÇÃO");
    installDatabase(Database::getDemo(), "DEMO");
    ?>

    <h2 style="color: blue;">✅ Concluído! Pode voltar ao Editor.</h2>
    <a href="editor_dinamico.php">Voltar para o Editor</a>
</body>

</html>