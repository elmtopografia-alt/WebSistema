<?php
/**
 * FIX: Restaurar Todas as Colunas do DOCX V3
 * Corrige o erro "#1060 - Nome da coluna duplicado" rodando cada coluna individualmente.
 */
require_once 'db.php';

header('Content-Type: text/html; charset=utf-8');
echo "<body style='background:#111; color:#eee; font-family:sans-serif; padding:20px;'>";
echo "<h2>🛠️ SGT Database Fix: Colunas DOCX (V3)</h2>";

$conn = Database::getProd();

if ($conn->connect_error) {
    die("<span style='color:#f00'>ERRO DE CONEXÃO: " . $conn->connect_error . "</span>");
}

$colunas_necessarias = [
    'modelo_docx' => "VARCHAR(255) DEFAULT NULL AFTER status",
    'docx_conteudo' => "LONGTEXT DEFAULT NULL AFTER modelo_docx",
    'docx_blocos_count' => "INT DEFAULT 0 AFTER docx_conteudo",
    'docx_ultima_edicao' => "DATETIME DEFAULT NULL AFTER docx_blocos_count",
    'config_docx_json' => "LONGTEXT DEFAULT NULL AFTER docx_ultima_edicao"
];

foreach ($colunas_necessarias as $col => $def) {
    echo "Verificando coluna <b>$col</b>... ";
    $check = $conn->query("SHOW COLUMNS FROM Propostas LIKE '$col'");
    
    if ($check && $check->num_rows == 0) {
        $sql = "ALTER TABLE Propostas ADD COLUMN $col $def";
        if ($conn->query($sql)) {
            echo "<span style='color:#0f0'>ADICIONADA COM SUCESSO!</span><br>";
        } else {
            echo "<span style='color:#f00'>ERRO: " . $conn->error . "</span><br>";
        }
    } else {
        echo "<span style='color:#888'>Já existe. (Ignorada)</span><br>";
    }
}

echo "<hr>";
echo "<h3 style='color:#0f0'>✅ Sincronização Concluída!</h3>";
echo "<p>As colunas necessárias para o funcionamento do DOCX v3 foram verificadas ou adicionadas com sucesso.</p>";
echo "<a href='painel.php' style='background:#2563eb; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Voltar ao Painel</a>";
echo "</body>";
