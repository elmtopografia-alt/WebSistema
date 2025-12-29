<?php
// ARQUIVO: esqueci_senha.php
session_start();

$msg = "";
$tipo_alerta = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Por favor, digite um e-mail válido.";
        $tipo_alerta = "danger";
    } else {
        // Conecta ao banco DEMO (Proposta)
        $conn = new mysqli('proposta.mysql.dbaas.com.br', 'proposta', 'Qtamaqmde5202@', 'proposta');

        if ($conn->connect_error) {
            $msg = "Erro de conexão. Tente mais tarde.";
            $tipo_alerta = "danger";
        } else {
            // Busca o usuário pelo e-mail
            $stmt = $conn->prepare("SELECT nome_completo, usuario, senha FROM Usuarios WHERE usuario = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $dados = $res->fetch_assoc();
                
                // Prepara o E-mail
                $assunto = "Recuperacao de Acesso - Demo Gera Proposta";
                $corpo = "Ola, " . $dados['nome_completo'] . "!\n\n";
                $corpo .= "Voce solicitou a recuperacao de seus dados de acesso ao ambiente de demonstracao.\n\n";
                $corpo .= "--------------------------------\n";
                $corpo .= "Usuario/Email: " . $dados['usuario'] . "\n";
                $corpo .= "Senha: " . $dados['senha'] . "\n";
                $corpo .= "Link: http://seu-site-aqui.com/login.php\n";
                $corpo .= "--------------------------------\n\n";
                $corpo .= "Atenciosamente,\nEquipe Gera Proposta";

                $headers = "From: nao-responda@seu-site-aqui.com";

                // Tenta enviar
                if (mail($email, $assunto, $corpo, $headers)) {
                    $msg = "Seus dados foram enviados para <strong>$email</strong>. Verifique sua caixa de entrada e SPAM.";
                    $tipo_alerta = "success";
                } else {
                    $msg = "Encontramos seu cadastro, mas houve um erro ao enviar o e-mail pelo servidor. Contate o suporte.";
                    $tipo_alerta = "warning";
                }
            } else {
                // Por segurança, damos uma mensagem genérica ou dizemos que não achamos (no demo pode dizer que não achou)
                $msg = "Este e-mail não consta em nossa base de demonstração.";
                $tipo_alerta = "danger";
            }
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Recuperar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; }</style>
</head>
<body>
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <h4>🔐 Recuperar Acesso</h4>
            <p class="text-muted small">Informe seu e-mail cadastrado no teste.</p>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-<?= $tipo_alerta ?>"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required placeholder="seu@email.com">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Enviar Dados</button>
                <a href="login.php" class="btn btn-outline-secondary">Voltar para Login</a>
            </div>
        </form>
    </div>
</body>
</html>