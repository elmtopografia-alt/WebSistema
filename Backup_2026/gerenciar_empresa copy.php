<?php
// ARQUIVO: gerenciar_empresa.php
// VERSÃO: MULTI-USUÁRIO (DADOS ISOLADOS)

session_start();
require_once 'db.php';
require_once 'valida_demo.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$readonly = $is_demo ? 'disabled' : ''; 
$msg = "";

// 1. Verifica se ESTE usuário já tem dados de empresa. Se não, cria.
$check = $conn->prepare("SELECT id_empresa FROM DadosEmpresa WHERE id_criador = ?");
$check->bind_param('i', $id_usuario);
$check->execute();
$res = $check->get_result();

if ($res->num_rows == 0) {
    // Cria registro padrão para este usuário
    $nome_padrao = $is_demo ? "Empresa Fictícia Demo" : "Minha Nova Empresa";
    $ins = $conn->prepare("INSERT INTO DadosEmpresa (id_criador, Empresa) VALUES (?, ?)");
    $ins->bind_param('is', $id_usuario, $nome_padrao);
    $ins->execute();
}

// 2. Processa Salvamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_demo) { die("Edição bloqueada no modo Demo."); }

    $sql = "UPDATE DadosEmpresa SET 
            Empresa=?, CNPJ=?, Endereco=?, Cidade=?, Estado=?, 
            Telefone=?, Celular=?, Whatsapp=?, 
            Banco=?, Agencia=?, Conta=?, PIX=? 
            WHERE id_criador = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssi", 
        $_POST['Empresa'], $_POST['CNPJ'], $_POST['Endereco'], $_POST['Cidade'], $_POST['Estado'],
        $_POST['Telefone'], $_POST['Celular'], $_POST['Whatsapp'],
        $_POST['Banco'], $_POST['Agencia'], $_POST['Conta'], $_POST['PIX'],
        $id_usuario // <--- AQUI ESTÁ O SEGREDO: Atualiza só o meu!
    );
    
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Seus dados foram salvos!</div>";
    }
}

// 3. Busca Meus Dados
$stmt = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ?");
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Minha Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 900px;">
        <h3>Minha Empresa (Dados para Proposta)</h3>
        <a href="index.php" class="btn btn-secondary mb-3">Voltar</a>
        <?= $msg ?>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <!-- Mantive o mesmo formulário visual, só mudei a lógica PHP acima -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Razão Social</label>
                            <input type="text" name="Empresa" class="form-control" value="<?= htmlspecialchars($emp['Empresa']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNPJ</label>
                            <input type="text" name="CNPJ" class="form-control" value="<?= htmlspecialchars($emp['CNPJ']??'') ?>" <?= $readonly ?>>
                        </div>
                        <!-- ... Adicione os outros campos conforme o arquivo anterior ... -->
                        <!-- Para economizar espaço, coloque os inputs restantes aqui igual ao arquivo anterior -->
                        
                        <div class="col-12 mt-3 text-end">
                            <?php if(!$is_demo): ?>
                                <button type="submit" class="btn btn-primary">Salvar Meus Dados</button>
                            <?php else: ?>
                                <span class="text-muted small">Modo Demo: Edição Bloqueada</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>