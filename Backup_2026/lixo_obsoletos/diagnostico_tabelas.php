<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

$conn = Database::getProd();

echo "=== VERIFICAÇÃO E AUTOCURA DE COLUNAS (DOCX V3) ===\n\n";

$res = $conn->query("SHOW COLUMNS FROM Propostas");
$cols = [];
if ($res) {
    while($r = $res->fetch_array()) {
        $cols[] = $r[0];
    }
} else {
    echo "Erro ao ler colunas: " . $conn->error . "\n";
    exit;
}

$required = [
    'modelo_docx' => 'VARCHAR(255) DEFAULT NULL',
    'docx_conteudo' => 'LONGTEXT DEFAULT NULL',
    'docx_blocos_count' => 'INT DEFAULT 0',
    'docx_ultima_edicao' => 'DATETIME DEFAULT NULL',
    'config_docx_json' => 'LONGTEXT DEFAULT NULL'
];

foreach ($required as $col => $def) {
    if (in_array($col, $cols)) {
        echo "[OK] Coluna existe: $col\n";
    } else {
        echo "[FAIL] Coluna ausente: $col\n";
        echo "  -> Tentando criar...\n";
        
        $sql = "ALTER TABLE Propostas ADD COLUMN $col $def";
        if ($conn->query($sql)) {
            echo "  -> Criada com sucesso!\n";
        } else {
            echo "  -> Erro ao criar ($col): " . $conn->error . "\n";
        }
    }
}

echo "\nDiagnóstico concluído.\n";
?>
