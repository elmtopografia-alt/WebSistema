<?php
// diagnostico_admin.php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "--- DIAGNÓSTICO DE ACESSO ADMIN ---\n\n";

try {
    $conn = Database::getProd();
    echo "Conexão com Banco de Dados: OK\n";
    
    $usuario_busca = 'edivaldo@elmtopografia.com.br';
    echo "Buscando usuário: $usuario_busca\n";
    
    $stmt = $conn->prepare("SELECT id_usuario, usuario, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
    $stmt->bind_param('s', $usuario_busca);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user) {
        echo "Usuário encontrado!\n";
        echo "ID: " . $user['id_usuario'] . "\n";
        echo "Nome: " . $user['nome_completo'] . "\n";
        echo "Perfil: " . $user['tipo_perfil'] . " (Deve ser 'admin')\n";
        echo "Validade: " . $user['validade_acesso'] . "\n";
        
        if ($user['tipo_perfil'] !== 'admin') {
            echo "\n⚠️ ATENÇÃO: O usuário existe, mas o perfil NÃO é 'admin'.\n";
        }
    } else {
        echo "\n❌ ERRO: Usuário não encontrado no banco de dados.\n";
        echo "Verifique se o e-mail está correto ou se você está conectado ao banco de dados certo.\n";
    }
    
} catch (Exception $e) {
    echo "ERRO TÉCNICO: " . $e->getMessage();
}
