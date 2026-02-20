<?php
/**
 * Atualizaçao de Schema para DOCX v3.0
 * Rode este script no servidor web para criar as colunas.
 */
require_once __DIR__ . '/ConnectionManager.php';
$conn = ConnectionManager::get();

echo "<h2>Atualizando o Banco de Dados (SGT DOCX v3.0)</h2>";

try {
    $conn->query('ALTER TABLE Propostas ADD COLUMN docx_conteudo LONGTEXT NULL');
    echo "<p style='color:green'>&#10004; Coluna <b>docx_conteudo</b> adicionada com sucesso.</p>";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "<p style='color:orange'>&#9888; Coluna <b>docx_conteudo</b> ja existe.</p>";
    } else {
        echo "<p style='color:red'>&#10008; Erro ao adicionar docx_conteudo: " . $e->getMessage() . "</p>";
    }
}

try {
    $conn->query('ALTER TABLE Propostas ADD COLUMN docx_blocos_count INT DEFAULT 0');
    echo "<p style='color:green'>&#10004; Coluna <b>docx_blocos_count</b> adicionada com sucesso.</p>";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "<p style='color:orange'>&#9888; Coluna <b>docx_blocos_count</b> ja existe.</p>";
    } else {
        echo "<p style='color:red'>&#10008; Erro ao adicionar docx_blocos_count: " . $e->getMessage() . "</p>";
    }
}

echo "<hr><p>Atualizacao do schema concluida. Pode fechar e apagar este arquivo.</p>";
?>
