<?php
// Nome do Arquivo: login_prod.php
// Função: Login Clientes com MIGRAÇÃO AUTOMÁTICA PARA HASH (Segurança Bancária).

session_start();
require_once 'config.php';
require_once 'db.php';

if (isset($_SESSION['usuario_id'])) { header("Location: painel.php"); exit; }

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitização: Remove caracteres perigosos do usuário
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha   = $_POST['senha']; // Senha não se sanitiza, pois pode ter símbolos propositais

    if (empty($usuario) || empty($senha)) {
        $erro = "Preencha usuário e senha.";
    } else {
        try {
            $conn = Database::getProd();
            
            // Busca o usuário pelo Login (Email ou Nome de Usuário)
            $stmt = $conn->prepare("SELECT id_usuario, usuario, senha, nome_completo, tipo_perfil, validade_acesso FROM Usuarios WHERE usuario = ? LIMIT 1");
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $senha_valida = false;
                $precisa_migrar = false;

                // 1. Tenta verificar como HASH (O jeito novo e seguro)
                if (password_verify($senha, $user['senha'])) {
                    $senha_valida = true;
                } 
                // 2. Se falhar, tenta como TEXTO PURO (Para usuários antigos não ficarem trancados)
                elseif ($user['senha'] === $senha) {
                    $senha_valida = true;
                    $precisa_migrar = true; // Marca para atualizar a segurança
                }

                if ($senha_valida) {
                    // VERIFICAÇÃO ADMINISTRATIVA (Admin não entra aqui)
                    if ($user['tipo_perfil'] === 'admin') {
                        $erro = "Acesso negado. Admins devem usar a porta de gestão.";
                    } else {
                        // VERIFICA VALIDADE
                        $hoje = new DateTime();
                        $val = new DateTime($user['validade_acesso'] ? $user['validade_acesso'] : '2000-01-01');
                        
                        if ($hoje > $val) {
                            $erro = "Sua assinatura venceu. Entre em contato.";
                        } else {
                            
                            // *** A MÁGICA DA SEGURANÇA ***
                            // Se a senha era velha (texto), criptografa AGORA e salva
                            if ($precisa_migrar) {
                                $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
                                $upd = $conn->prepare("UPDATE Usuarios SET senha = ? WHERE id_usuario = ?");
                                $upd->bind_param('si', $novo_hash, $user['id_usuario']);
                                $upd->execute();
                            }

                            // Login Sucesso
                            session_regenerate_id(true);
                            $_SESSION['usuario_id']    = $user['id_usuario'];
                            $_SESSION['usuario_nome']  = $user['nome_completo'];
                            $_SESSION['perfil']        = $user['tipo_perfil'];
                            $_SESSION['ambiente']      = 'producao'; 
                            $_SESSION['origem_login']  = 'cliente';
                            
                            header("Location: painel.php");
                            exit;
                        }
                    }
                } else {
                    $erro = "Senha incorreta.";
                }
            } else {
                $erro = "Usuário não encontrado.";
            }
        } catch (Exception $e) { $erro = "Erro técnico."; }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Área do Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#198754;display:flex;align-items:center;justify-content:center;height:100vh}</style>
</head>
<body>
    <div class="card border-0 shadow" style="width:400px">
        <div class="card-body p-4">
            <h4 class="text-success text-center fw-bold mb-3">Área do Cliente</h4>
            <?php if($erro): ?><div class="alert alert-danger py-2 small text-center"><?php echo $erro; ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="mb-3"><label class="fw-bold small">USUÁRIO</label><input type="text" name="usuario" class="form-control" required autofocus></div>
                <div class="mb-3"><label class="fw-bold small">SENHA</label><input type="password" name="senha" class="form-control" required></div>
                <button class="btn btn-success w-100 fw-bold">ENTRAR</button>
            </form>
            
            <div class="text-center mt-3"><a href="index.php" class="text-decoration-none text-muted small">&larr; Voltar ao Site</a></div>
        </div>
    </div>
</body>
</html>