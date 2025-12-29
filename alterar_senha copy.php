<?php
// Nome do Arquivo: alterar_senha.php
// Função: Alteração de CREDENCIAIS (Login/Email + Senha) com verificação de duplicidade e força.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Validação de Acesso
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

$erro = '';
$sucesso = '';

// 2. Busca os dados atuais para preencher o formulário
try {
    $stmtLoad = $conn->prepare("SELECT usuario FROM Usuarios WHERE id_usuario = ?");
    $stmtLoad->bind_param('i', $id_usuario);
    $stmtLoad->execute();
    $resLoad = $stmtLoad->get_result();
    $dados_atuais = $resLoad->fetch_assoc();
} catch (Exception $e) {
    die("Erro ao carregar dados.");
}

// 3. Processamento do Formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_login  = trim($_POST['novo_usuario'] ?? '');
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha  = $_POST['nova_senha'] ?? '';
    $conf_senha  = $_POST['conf_senha'] ?? '';

    // Validações Básicas
    if (empty($novo_login) || empty($senha_atual) || empty($nova_senha) || empty($conf_senha)) {
        $erro = "Todos os campos são obrigatórios para a segurança da conta.";
    } elseif (!filter_var($novo_login, FILTER_VALIDATE_EMAIL)) {
        $erro = "O novo login deve ser um e-mail válido.";
    } elseif ($nova_senha !== $conf_senha) {
        $erro = "A nova senha e a confirmação não conferem.";
    } elseif (strlen($nova_senha) < 8 || !preg_match("/[A-Z]/", $nova_senha) || !preg_match("/[a-z]/", $nova_senha) || !preg_match("/[0-9]/", $nova_senha) || !preg_match("/[\W]/", $nova_senha)) {
        $erro = "Senha fraca! Aumente a segurança: Mínimo 8 dígitos, Letra Maiúscula, Minúscula, Número e Símbolo.";
    } else {
        try {
            // 4. Verificação de Duplicidade (Não pode pegar e-mail de outro)
            // Procura se existe alguém com esse e-mail QUE NÃO SEJA EU
            $stmtDup = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ? AND id_usuario != ?");
            $stmtDup->bind_param('si', $novo_login, $id_usuario);
            $stmtDup->execute();
            if ($stmtDup->get_result()->num_rows > 0) {
                $erro = "Este e-mail/login já está em uso por outro usuário. Escolha outro.";
            } else {
                // 5. Verifica a Senha Atual (Guardião da Segurança)
                $stmtAuth = $conn->prepare("SELECT senha FROM Usuarios WHERE id_usuario = ? LIMIT 1");
                $stmtAuth->bind_param('i', $id_usuario);
                $stmtAuth->execute();
                $userAuth = $stmtAuth->get_result()->fetch_assoc();

                if ($userAuth) {
                    $senha_correta = false;
                    // Suporta Hash e Texto Puro (Híbrido)
                    if (password_verify($senha_atual, $userAuth['senha'])) {
                        $senha_correta = true;
                    } elseif ($userAuth['senha'] === $senha_atual) {
                        $senha_correta = true;
                    }

                    if ($senha_correta) {
                        // 6. TUDO OK: Atualiza Login E Senha (Criptografada)
                        $nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                        $stmtUp = $conn->prepare("UPDATE Usuarios SET usuario = ?, senha = ? WHERE id_usuario = ?");
                        $stmtUp->bind_param('ssi', $novo_login, $nova_hash, $id_usuario);
                        
                        if ($stmtUp->execute()) {
                            $sucesso = "Credenciais blindadas com sucesso! Use o novo login e senha no próximo acesso.";
                            // Atualiza a sessão para refletir o novo nome imediatamente
                            $_SESSION['usuario_login'] = $novo_login;
                            $dados_atuais['usuario'] = $novo_login; // Atualiza visualização
                        } else {
                            $erro = "Erro ao atualizar no banco de dados.";
                        }
                    } else {
                        $erro = "A Senha Atual informada está incorreta. Alteração negada.";
                    }
                }
            }
        } catch (Exception $e) {
            $erro = "Erro técnico de segurança: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Segurança e Acesso | SGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card-security { border: none; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .form-control:focus { border-color: #198754; box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="painel.php"><i class="bi bi-arrow-left me-2"></i>Voltar ao Painel</a>
            <span class="navbar-text text-white"><i class="bi bi-shield-check me-2"></i>Blindagem de Conta</span>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                <div class="card card-security">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">Alterar Credenciais de Acesso</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if($erro): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?php echo $erro; ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if($sucesso): ?>
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div><?php echo $sucesso; ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-warning small border-0 bg-warning bg-opacity-10 text-warning-emphasis">
                            <i class="bi bi-info-circle me-1"></i> 
                            Para sua segurança, ao alterar seus dados, exigimos a redefinição da senha para um padrão forte.
                        </div>

                        <form method="POST">
                            
                            <!-- SEÇÃO 1: IDENTIFICAÇÃO -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">Novo Login (E-mail)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-fill"></i></span>
                                    <input type="email" name="novo_usuario" class="form-control" value="<?php echo htmlspecialchars($dados_atuais['usuario']); ?>" required>
                                </div>
                                <div class="form-text">Este será seu usuário para entrar no sistema.</div>
                            </div>

                            <!-- SEÇÃO 2: VALIDAÇÃO -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary">Senha Atual (Para confirmar)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-unlock-fill"></i></span>
                                    <input type="password" name="senha_atual" class="form-control" required placeholder="Digite sua senha atual">
                                </div>
                            </div>

                            <hr>

                            <!-- SEÇÃO 3: NOVA SENHA -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-success">Nova Senha Forte</label>
                                <input type="password" name="nova_senha" class="form-control" required>
                                <div class="form-text text-danger small">
                                    <strong>Requisitos:</strong> Mínimo 8 caracteres, 1 Maiúscula, 1 Minúscula, 1 Número e 1 Símbolo (@#$%).
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-success">Repetir Nova Senha</label>
                                <input type="password" name="conf_senha" class="form-control" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success fw-bold py-2 shadow-sm">
                                    SALVAR NOVAS CREDENCIAIS
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        Ambiente Seguro: <strong><?php echo $is_demo ? 'DEMO' : 'PRODUÇÃO'; ?></strong>
                    </small>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>