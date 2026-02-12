<?php
$user = 'root';
$pass = '';
$db = 'sistemas_web';

echo "--- Diagnosticando Conexao ---<br>\n";

$hosts = ['127.0.0.1', 'localhost', '::1'];

foreach ($hosts as $test_host) {
    echo "Tentando conectar em <b>$test_host</b>... ";
    try {
        $dsn = "mysql:host=$test_host;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 2]);
        echo "<span style='color:green'>SUCESSO!</span><br>\n";
        
        $stat = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
        echo "Status: $stat<br>\n";
    } catch (PDOException $e) {
        echo "<span style='color:red'>FALHA: " . $e->getMessage() . "</span><br>\n";
    }
    echo "<hr>\n";
}
?>
