<?php
// Script de Diagnóstico de Erros (HTTP 500)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Erros</h1>";

echo "<h2>1. Verificando Caminhos</h2>";
$paths = [
    '../config.php',
    '../db.php',
    'equipamentos_mapper.php',
    'proposta_template.php',
    'gerar_proposta.php'
];

foreach ($paths as $p) {
    if (file_exists($p)) {
        echo "<p style='color:green'>[OK] $p encontrado.</p>";
    } else {
        echo "<p style='color:red'>[ERRO] $p NÃO encontrado.</p>";
    }
}

echo "<h2>2. Testando Inclusão de Config</h2>";
try {
    if (file_exists('../config.php')) {
        require_once '../config.php';
        echo "<p style='color:green'>[OK] config.php incluído com sucesso.</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>[ERRO] Falha ao incluir config.php: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Testando Conexão DB</h2>";
try {
    if (file_exists('../db.php')) {
        require_once '../db.php';
        if (class_exists('Database')) {
            $conn = Database::getProd();
            if ($conn) {
                echo "<p style='color:green'>[OK] Conexão com Banco de Dados estabelecida.</p>";
            }
        } else {
            echo "<p style='color:red'>[ERRO] Classe Database não encontrada.</p>";
        }
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>[ERRO FATAL] " . $e->getMessage() . "</p>";
}

echo "<h2>4. Verificando Colunas no Banco</h2>";
try {
    if (isset($conn)) {
        $res = $conn->query("SHOW COLUMNS FROM Propostas LIKE 'marca_veiculo'");
        if ($res && $res->num_rows > 0) {
            echo "<p style='color:green'>[OK] Coluna 'marca_veiculo' existe.</p>";
        } else {
            echo "<p style='color:red'>[ALERTA] Coluna 'marca_veiculo' NÃO encontrada. Você rodou o SQL?</p>";
        }
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>[ERRO] " . $e->getMessage() . "</p>";
}
?>
