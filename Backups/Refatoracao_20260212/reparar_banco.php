<?php
// ARQUIVO: reparar_banco.php
// FUNÇÃO: Força a criação das colunas faltantes no banco de PRODUÇÃO

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔧 Iniciando Reparo do Banco de Produção (demanda)...</h2>";

// 1. Conecta no Banco DEMANDA (Produção)
$host = 'demanda.mysql.dbaas.com.br';
$user = 'demanda';
$pass = 'Qtamaqmde5202@';
$base = 'demanda';

$conn = new mysqli($host, $user, $pass, $base);

if ($conn->connect_error) {
    die("<h3 style='color:red'>Erro de Conexão: " . $conn->connect_error . "</h3>");
}

echo "<p>✅ Conectado ao banco <strong>demanda</strong>.</p>";

// 2. Lista de Comandos para Rodar
$comandos = [
    // Tabela Usuarios
    "ALTER TABLE Usuarios ADD COLUMN setup_concluido TINYINT(1) DEFAULT 1",
    "ALTER TABLE Usuarios ADD COLUMN ambiente VARCHAR(20) DEFAULT 'producao'",
    
    // Tabelas para Multi-Usuário (Garante que existam)
    "ALTER TABLE DadosEmpresa ADD COLUMN id_criador INT DEFAULT NULL",
    "ALTER TABLE Clientes ADD COLUMN id_criador INT DEFAULT NULL"
];

// 3. Executa um por um
foreach ($comandos as $sql) {
    echo "<hr>Tentando executar: <br><code>$sql</code><br>";
    
    if ($conn->query($sql) === TRUE) {
        echo "<span style='color:green; font-weight:bold;'>SUCESSO: Coluna criada!</span>";
    } else {
        // Se der erro, verificamos se é porque já existe (Erro 1060)
        if ($conn->errno == 1060) {
            echo "<span style='color:blue;'>INFO: A coluna já existia. (Ignorado)</span>";
        } else {
            echo "<span style='color:red; font-weight:bold;'>ERRO: " . $conn->error . "</span>";
        }
    }
}

echo "<hr><h2>🏁 Processo Finalizado.</h2>";
echo "<p>Tente fazer o cadastro novamente agora.</p>";
echo "<a href='cadastrar.php' style='background:#28a745; color:white; padding:10px 20px; text-decoration:none;'>Ir para Cadastro</a>";

$conn->close();
?>