<?php
// Script de Importação Manual (Do Relatório TXT para MySQL)
// Uso: php importar_relatorio_txt.php

// require_once 'core/conexao.php'; // Removido para usar conexão autônoma abaixo

// Se não tiver conexão pronta, usamos uma local rápida
$host = '127.0.0.1';
$db   = 'sistemas_web';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

$arquivo = 'relatorio_prospeccao_brasil_v1.txt';
if (!file_exists($arquivo)) {
    die("Arquivo $arquivo não encontrado.");
}

$linhas = file($arquivo);
$count = 0;

echo "Iniciando importação...\n";

foreach ($linhas as $linha) {
    // Pula cabeçalhos e linhas de decoração
    if (strpos($linha, '|') === false) continue;
    if (strpos($linha, 'Empresa') !== false) continue;
    if (strpos($linha, '---') !== false) continue;

    // Parser da tabela Markdown
    $cols = array_map('trim', explode('|', $linha));
    
    // Esperado: | Vazio | Empresa | Estado | Site | Canal | Contato | Vazio |
    if (count($cols) < 6) continue;

    $empresa = $cols[1];
    $estado = $cols[2];
    $site = $cols[3];
    $canal = $cols[4];
    $contato = $cols[5];

    // Limpa dados
    if (empty($empresa) || empty($site)) continue;

    try {
        $stmt = $pdo->prepare("INSERT INTO leads_prospeccao (nome_empresa, site_origem, ramo_atuacao, whatsapp, metodo_captura, status_envio) VALUES (?, ?, ?, ?, ?, 'PENDENTE')");
        
        // Simples detecção de tipo de contato para o campo correto
        $metodo = 'manual_import';
        if (stripos($canal, 'WhatsApp') !== false) $metodo = 'whatsapp';
        if (stripos($canal, 'Email') !== false) $metodo = 'email';
        if (stripos($canal, 'Formulário') !== false) $metodo = 'form';

        $stmt->execute([$empresa, $site, "Topografia ($estado)", $contato, $metodo]);
        echo "✅ Importado: $empresa\n";
        $count++;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            echo "⚠️ Já existe: $empresa\n";
        } else {
            echo "❌ Erro ao importar $empresa: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nImportação concluída! Total de novos leads: $count\n";
?>
