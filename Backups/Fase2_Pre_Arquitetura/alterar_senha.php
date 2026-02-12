<?php
// O Nome do Arquivo: alterar_senha.php
// Função: Alteração com Checklist Visual de Segurança.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }
$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();
$erro = ''; $sucesso = '';

// Dados
$stmtLoad = $conn->prepare("SELECT usuario FROM Usuarios WHERE id_usuario = ?");
$stmtLoad->bind_param('i', $id_usuario); $stmtLoad->execute();
$dados_atuais = $stmtLoad->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_email = trim($_POST['novo_usuario']);
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $conf_senha = $_POST['conf_senha'];

    if (!filter_var($novo_email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    } elseif ($nova_senha !== $conf_senha) {
        $erro = "Senhas não conferem.";
    } elseif (strlen($nova_senha) < 8 || !preg_match("/[A-Z]/", $nova_senha) || !preg_match("/[a-z]/", $nova_senha) || !preg_match("/[0-9]/", $nova_senha) || !preg_match("/[\W]/", $nova_senha)) {
        $erro = "Senha fraca! Siga o checklist.";
    } else {
        // (Lógica de update mantida, foco no visual)
        try {
            $stmtAuth = $conn->prepare("SELECT senha FROM Usuarios WHERE id_usuario = ?");
            $stmtAuth->bind_param('i', $id_usuario); $stmtAuth->execute(); $userAuth = $stmtAuth->get_result()->fetch_assoc();
            
            $senha_ok = password_verify($senha_atual, $userAuth['senha']) || ($userAuth['senha'] === $senha_atual);
            
            if ($senha_ok) {
                $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $up = $conn->prepare("UPDATE Usuarios SET usuario=?, senha=? WHERE id_usuario=?");
                $up->bind_param('ssi', $novo_email, $hash, $id_usuario);
                if ($up->execute()) {
                    $sucesso = "Atualizado com sucesso!";
                    $_SESSION['usuario_login'] = $novo_email;
                    $dados_atuais['usuario'] = $novo_email;
                }
            } else { $erro = "Senha atual incorreta."; }
        } catch(Exception $e) { $erro = "Erro técnico."; }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Segurança</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .pwd-rules { font-size: 0.8rem; background: #f8f9fa; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px; }
        .rule-item { margin-bottom: 2px; color: #6c757d; display: flex; align-items: center; gap: 5px; }
        .rule-item.valid { color: #198754; font-weight: bold; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4"><div class="container"><a class="navbar-brand" href="painel.php"><i class="bi bi-arrow-left"></i> Painel</a><span class="text-white">Segurança</span></div></nav>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Alterar Credenciais</div>
                    <div class="card-body p-4">
                        <?php if($erro): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
                        <?php if($sucesso): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>

                        <form method="POST" id="formChange">
                            <div class="mb-3">
                                <label class="form-label">E-mail de Acesso</label>
                                <input type="email" name="novo_usuario" class="form-control" value="<?php echo htmlspecialchars($dados_atuais['usuario']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Senha Atual (Para confirmar)</label>
                                <input type="password" name="senha_atual" class="form-control" required>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary">Nova Senha Forte</label>
                                <input type="password" name="nova_senha" id="senhaInput" class="form-control" required>
                                
                                <div class="pwd-rules">
                                    <div class="rule-item" id="r-len"><i class="bi bi-circle"></i> 8 Caracteres</div>
                                    <div class="rule-item" id="r-up"><i class="bi bi-circle"></i> Maiúscula</div>
                                    <div class="rule-item" id="r-low"><i class="bi bi-circle"></i> Minúscula</div>
                                    <div class="rule-item" id="r-num"><i class="bi bi-circle"></i> Número</div>
                                    <div class="rule-item" id="r-sym"><i class="bi bi-circle"></i> Símbolo (@#$%)</div>
                                </div>
                            </div>
                            <div class="mb-3"><label class="form-label">Repetir Nova Senha</label><input type="password" name="conf_senha" class="form-control" required></div>
                            <button class="btn btn-success w-100 fw-bold">SALVAR</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const pass = document.getElementById('senhaInput');
        pass.addEventListener('input', function() {
            const v = pass.value;
            const rules = [
                { id: 'r-len', valid: v.length >= 8 },
                { id: 'r-up',  valid: /[A-Z]/.test(v) },
                { id: 'r-low', valid: /[a-z]/.test(v) },
                { id: 'r-num', valid: /[0-9]/.test(v) },
                { id: 'r-sym', valid: /[\W_]/.test(v) }
            ];
            rules.forEach(r => {
                const el = document.getElementById(r.id);
                if(r.valid) {
                    el.classList.add('valid');
                    el.querySelector('i').className = 'bi bi-check-circle-fill';
                } else {
                    el.classList.remove('valid');
                    el.querySelector('i').className = 'bi bi-circle';
                }
            });
        });
        document.getElementById('formChange').addEventListener('submit', function(e) {
            const v = pass.value;
            if (v.length < 8 || !/[A-Z]/.test(v) || !/[a-z]/.test(v) || !/[0-9]/.test(v) || !/[\W_]/.test(v)) {
                e.preventDefault();
                alert('Sua nova senha é fraca. Verifique o checklist.');
                pass.focus();
            }
        });
    </script>
</body>
</html>