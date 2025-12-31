<?php
// admin_criar_demo.php
session_start();

// Só admin logado pode acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['ambiente'] !== 'producao') {
    die("Acesso negado. Apenas administradores do ambiente de Produção.");
}

$msg = "";
$novo_usuario = "";
$nova_senha = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gera usuário e senha aleatórios
    $novo_usuario = "demo" . rand(100, 999);
    $nova_senha = rand(100000, 999999); // Senha simples de 6 números
    $nome = $_POST['nome_interessado'];

    // Conecta no banco DEMO para inserir
    $conn_demo = new mysqli('proposta.mysql.dbaas.com.br', 'proposta', 'Qtamaqmde5202@', 'proposta');
    
    if ($conn_demo->connect_error) {
        $msg = "<div class='alert alert-danger'>Erro ao conectar no banco Demo.</div>";
    } else {
        // Validade NULL para ativar no primeiro login
        $stmt = $conn_demo->prepare("INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, validade_acesso) VALUES (?, ?, ?, 1, NULL)");
        $stmt->bind_param('sss', $novo_usuario, $nova_senha, $nome);
        
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>
                        <h4>✅ Usuário Demo Criado!</h4>
                        <p>Envie estes dados para o cliente:</p>
                        <ul>
                            <li><strong>Link:</strong> seu-site.com/login.php</li>
                            <li><strong>Usuário:</strong> $novo_usuario</li>
                            <li><strong>Senha:</strong> $nova_senha</li>
                        </ul>
                        <p><em>Os 5 dias começarão a contar quando ele fizer o primeiro login.</em></p>
                    </div>";
        } else {
            $msg = "<div class='alert alert-danger'>Erro ao criar: " . $conn_demo->error . "</div>";
        }
        $conn_demo->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Gerar Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Gerador de Acesso Demonstração</h5>
            </div>
            <div class="card-body">
                <?= $msg ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Nome do Interessado / Cliente</label>
                        <input type="text" name="nome_interessado" class="form-control" required placeholder="Ex: João da Silva">
                    </div>
                    <button type="submit" class="btn btn-primary">Gerar Acesso Temporário</button>
                    <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>