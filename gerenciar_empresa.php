<?php
// ARQUIVO: gerenciar_empresa.php
// VERSÃO: UX MELHORADA PARA DEMO (Explica por que está bloqueado)

session_start();
require_once 'config.php';
require_once 'db.php';
require_once 'valida_demo.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['usuario_id'];
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
$readonly = $is_demo ? 'disabled' : ''; 

// 1. Garante que existe registro (para não dar erro no form)
$check = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $id_usuario");
if ($check->num_rows == 0) {
    // Se não existir, cria vazio agora para não quebrar a tela
    $conn->query("INSERT INTO DadosEmpresa (id_criador, Empresa) VALUES ($id_usuario, 'Minha Empresa')");
    $check = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $id_usuario");
}
$emp = $check->fetch_assoc();

// 2. Processa Salvamento (Só se não for Demo)
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_demo) {
        $msg = "<div class='alert alert-warning'>⚠️ No modo demonstração, os dados da empresa são fixos.</div>";
    } else {
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
            $id_usuario
        );
        
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>✅ Dados atualizados com sucesso!</div>";
            // Atualiza dados na memória
            $emp = $conn->query("SELECT * FROM DadosEmpresa WHERE id_criador = $id_usuario")->fetch_assoc();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Minha Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 900px;">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fa-solid fa-building text-primary me-2"></i>Minha Empresa</h3>
            <a href="index.php" class="btn btn-secondary">Voltar</a>
        </div>

        <?= $msg ?>

        <?php if($is_demo): ?>
        <div class="alert alert-info border-info d-flex align-items-center shadow-sm">
            <i class="fa-solid fa-circle-info fa-2x me-3 text-info"></i>
            <div>
                <strong>Modo de Visualização</strong><br>
                Estes são dados de exemplo. Na versão completa, você poderá cadastrar seu CNPJ, Logo e Dados Bancários reais para saírem na proposta.
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST">
                    <h5 class="mb-3 text-muted border-bottom pb-2">Dados Cadastrais</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Razão Social</label>
                            <input type="text" name="Empresa" class="form-control" value="<?= htmlspecialchars($emp['Empresa']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNPJ</label>
                            <input type="text" name="CNPJ" class="form-control" value="<?= htmlspecialchars($emp['CNPJ']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="Endereco" class="form-control" value="<?= htmlspecialchars($emp['Endereco']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="Cidade" class="form-control" value="<?= htmlspecialchars($emp['Cidade']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">UF</label>
                            <input type="text" name="Estado" class="form-control" value="<?= htmlspecialchars($emp['Estado']??'') ?>" <?= $readonly ?>>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3 text-muted border-bottom pb-2">Contato</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Telefone Fixo</label>
                            <input type="text" name="Telefone" class="form-control" value="<?= htmlspecialchars($emp['Telefone']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Celular</label>
                            <input type="text" name="Celular" class="form-control" value="<?= htmlspecialchars($emp['Celular']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="Whatsapp" class="form-control" value="<?= htmlspecialchars($emp['Whatsapp']??'') ?>" <?= $readonly ?>>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3 text-muted border-bottom pb-2">Dados Bancários (Para Proposta)</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Banco</label>
                            <input type="text" name="Banco" class="form-control" value="<?= htmlspecialchars($emp['Banco']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Agência</label>
                            <input type="text" name="Agencia" class="form-control" value="<?= htmlspecialchars($emp['Agencia']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Conta</label>
                            <input type="text" name="Conta" class="form-control" value="<?= htmlspecialchars($emp['Conta']??'') ?>" <?= $readonly ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Chave PIX</label>
                            <input type="text" name="PIX" class="form-control" value="<?= htmlspecialchars($emp['PIX']??'') ?>" <?= $readonly ?>>
                        </div>
                    </div>
                    
                    <div class="col-12 mt-4 text-end">
                        <?php if(!$is_demo): ?>
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-save me-2"></i> Salvar Meus Dados</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary" disabled><i class="fa-solid fa-lock me-2"></i> Edição Bloqueada (Demo)</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>