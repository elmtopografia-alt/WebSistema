<?php
/**
 * FIX: Restaurar Colunas do Editor Avançado
 * Corrige o erro "Unknown column 'modelo_docx'" e garante consistência.
 */
require_once 'db.php';

header('Content-Type: text/html; charset=utf-8');
echo "<body style='background:#111; color:#eee; font-family:sans-serif; padding:20px;'>";
echo "<h2>🛠️ SGT Database Fix: Propostas</h2>";

$conn = Database::getProd();

$colunas_necessarias = [
    'modelo_docx' => "VARCHAR(255) DEFAULT NULL AFTER id_servico",
    'config_docx_json' => "LONGTEXT DEFAULT NULL AFTER modelo_docx"
];

foreach ($colunas_necessarias as $col => $def) {
    echo "Verificando coluna <b>$col</b>... ";
    $check = $conn->query("SHOW COLUMNS FROM Propostas LIKE '$col'");
    
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE Propostas ADD COLUMN $col $def";
        if ($conn->query($sql)) {
            echo "<span style='color:#0f0'>ADICIONADA COM SUCESSO!</span><br>";
        } else {
            echo "<span style='color:#f00'>ERRO: " . $conn->error . "</span><br>";
        }
    } else {
        echo "<span style='color:#888'>Já existe.</span><br>";
    }
}

echo "<hr>";
echo "<h3 style='color:#0f0'>✅ Migração Concluída!</h3>";
echo "<p>O fluxo entre Criar Proposta e o Editor Avançado agora deve funcionar perfeitamente.</p>";
echo "<a href='painel.php' style='background:#2563eb; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Voltar ao Painel</a>";
echo "</body>";
