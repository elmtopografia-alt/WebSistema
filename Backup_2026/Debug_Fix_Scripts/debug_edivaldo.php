<?php
// Arquivo: debug_edivaldo.php
require_once 'config.php';
require_once 'db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "Diagnóstico para 'edivaldo@elmtopografia.com.br' em PRODUÇÃO\n";
echo "---------------------------------------------------------\n";

$conn = Database::getProd();
$u = 'edivaldo@elmtopografia.com.br';

// 1. Busca bruta
$sql = "SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil FROM Usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $u);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    echo "❌ Usuário NÃO ENCONTRADO no banco.\n";
    echo "Tentando buscar qualquer coisa parecida com 'edivaldo'...\n";
    $res2 = $conn->query("SELECT id_usuario, usuario FROM Usuarios WHERE usuario LIKE '%edivaldo%'");
    while($r = $res2->fetch_assoc()) {
        echo " - Encontrei: [{$r['id_usuario']}] {$r['usuario']}\n";
    }
} else {
    echo "✅ Usuário ENCONTRADO:\n";
    echo "ID: " . $user['id_usuario'] . "\n";
    echo "Nome: " . $user['usuario'] . "\n";
    echo "Perfil: " . $user['tipo_perfil'] . "\n";
    echo "Senha (DB): " . $user['senha'] . "\n"; // Mostra hash ou texto
    
    $senha_teste = 'Elm@2026';
    echo "\nTestando senha esperada: '$senha_teste'\n";
    
    // Teste 1: Hash
    if (password_verify($senha_teste, $user['senha'])) {
        echo "✅ password_verify: SUCESSO (É um hash válido)\n";
    } else {
        echo "❌ password_verify: FALHA\n";
    }
    
    // Teste 2: Texto Puro
    if ($user['senha'] === $senha_teste) {
        echo "✅ Comparação Texto Puro: SUCESSO (Senha não criptografada)\n";
    } else {
        echo "❌ Comparação Texto Puro: FALHA\n";
    }
    
    // Opção de Reset Emergencial
    echo "\n---------------------------------------------------------\n";
    echo "Tentando resetar forçado para 'Elm@2026' agora...\n";
    
    $novo_hash = password_hash($senha_teste, PASSWORD_DEFAULT);
    $up = $conn->prepare("UPDATE Usuarios SET senha = ? WHERE id_usuario = ?");
    $up->bind_param('si', $novo_hash, $user['id_usuario']);
    
    if ($up->execute()) {
        echo "✅ UPDATE realizado. Senha redefinida para hash novo.\n";
        echo "Novo Hash: " . $novo_hash . "\n";
    } else {
        echo "❌ Falha no UPDATE: " . $conn->error . "\n";
    }
}
echo "</pre>";
?>
