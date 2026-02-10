<?php
require_once 'db.php';
$conn = Database::getProd();

require_once 'config.php';
$conn = Database::getProd();

echo "<style>table { border-collapse: collapse; width: 100%; margin-bottom: 20px; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; } h2 { font-family: sans-serif; }</style>";

$currentUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
echo "<p>Você está acessando: <strong>$currentUrl</strong></p>";
echo "<a href='painel.php'>Voltar ao Painel</a>";
echo "<h1>Visualizador Direto do Banco de Dados</h1>";

// 1. Tipo_Servicos
echo "<h2>1. Tabela: Tipo_Servicos</h2>";
$res = $conn->query("SELECT * FROM Tipo_Servicos");
echo "<table><tr><th>ID</th><th>Nome</th><th>Descrição (início)</th></tr>";
while ($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['id_servico']}</td><td>{$row['nome']}</td><td>" . substr($row['descricao'], 0, 50) . "...</td></tr>";
}
echo "</table>";

// 2. proposal_block_templates
echo "<h2>2. Tabela: proposal_block_templates</h2>";
$res = $conn->query("SELECT * FROM proposal_block_templates ORDER BY `order`");
echo "<table><tr><th>ID</th><th>Ordem</th><th>Slug</th><th>Nome</th><th>Categoria</th></tr>";
while ($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['order']}</td><td>{$row['slug']}</td><td>{$row['name']}</td><td>{$row['category']}</td></tr>";
}
echo "</table>";

// 3. proposal_content_variations
echo "<h2>3. Tabela: proposal_content_variations</h2>";
$res = $conn->query("SELECT * FROM proposal_content_variations ORDER BY block_slug");
echo "<table><tr><th>ID</th><th>Bloco (Slug)</th><th>Variação</th><th>Conteúdo (Resumo)</th></tr>";
while ($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['block_slug']}</td><td>{$row['variation_name']}</td><td>" . htmlspecialchars(substr(strip_tags($row['content_text']), 0, 100)) . "...</td></tr>";
}
echo "</table>";
?>
