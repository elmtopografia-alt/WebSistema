<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'config.php';
require 'db.php';

$conn = Database::getProd();

$sql = "CREATE TABLE IF NOT EXISTS Proposta_Cronograma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proposta INT NOT NULL,
    ordem INT DEFAULT 0,
    nome_etapa VARCHAR(255),
    descricao TEXT,
    prazo VARCHAR(100),
    INDEX (id_proposta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// FORÇA RECRIAR
$conn->query("DROP TABLE IF EXISTS Proposta_Cronograma");

if ($conn->query($sql) === TRUE) {
    echo "<h1>SUCESSO: Tabela Proposta_Cronograma CRIADA (Drop/Create).</h1>";
} else {
    echo "<h1>ERRO AO CRIAR: " . $conn->error . "</h1>";
}

echo "<h3>Verificando existência:</h3>";
$check = $conn->query("SELECT count(*) as qtd FROM Proposta_Cronograma");
if ($check) {
    $row = $check->fetch_assoc();
    echo "Tabela existe com " . $row['qtd'] . " registros.<br>";
} else {
    echo "ERRO AO CONSULTAR: " . $conn->error . "<br>";
}
?>
