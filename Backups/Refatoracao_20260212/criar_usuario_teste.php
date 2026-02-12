<?php
// Arquivo: criar_usuario_teste.php
// Função: Criar usuário PRO (ELM Topografia) com senha criptografada

require_once 'config.php';
require_once 'db.php';

// Dados solicitados (Atualizados)
$novo_usuario = 'elmtopografia@gmail.com';
$nova_senha = 'Elm$1955$';
$nome_completo = 'ELM Topografia';
$perfil = 'cliente'; // Perfil de acesso ao painel de produção
$validade = date('Y-m-d', strtotime('+10 years')); // Validade longa

try {
    $conn = Database::getProd();
    
    // Verifica se já existe
    $stmt = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
    $stmt->bind_param('s', $novo_usuario);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        echo "<div style='font-family: sans-serif; padding: 20px; background: #fff3cd; color: #856404; border-radius: 8px; max-width: 500px; margin: 50px auto;'>";
        echo "<h1>⚠️ Usuário Já Existe</h1>";
        echo "<p>O usuário <strong>$novo_usuario</strong> já está cadastrado.</p>";
        echo "<p>Se quiser resetar a senha, exclua o usuário no banco ou solicite um script de update.</p>";
        echo "</div>";
        exit;
    }

    // Criptografia Segura (Hash)
    $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    // Insere no Banco
    $sql = "INSERT INTO Usuarios (usuario, senha, nome_completo, tipo_perfil, validade_acesso) VALUES (?, ?, ?, ?, ?)";
    $stmtIns = $conn->prepare($sql);
    $stmtIns->bind_param('sssss', $novo_usuario, $hash, $nome_completo, $perfil, $validade);
    
    if($stmtIns->execute()) {
        echo "<div style='font-family: sans-serif; padding: 20px; background: #d1e7dd; color: #0f5132; border-radius: 8px; max-width: 500px; margin: 50px auto;'>";
        echo "<h1>✅ Usuário Atualizado!</h1>";
        echo "<p>Credenciais configuradas com sucesso.</p>";
        echo "<hr>";
        echo "<ul>";
        echo "<li><strong>Login:</strong> $novo_usuario</li>";
        echo "<li><strong>Senha:</strong> (Criptografada)</li>";
        echo "<li><strong>Validade:</strong> Até " . date('d/m/Y', strtotime($validade)) . "</li>";
        echo "</ul>";
        echo "<p>Pode acessar o sistema.</p>";
        echo "<a href='login_prod.php' style='display: inline-block; background: #198754; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Ir para Login</a>";
        echo "</div>";
    } else {
        echo "Erro ao inserir: " . $conn->error;
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
