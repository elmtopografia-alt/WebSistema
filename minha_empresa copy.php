<?php
// Nome do Arquivo: minha_empresa.php
// Função: Configuração da Empresa com MENU UNIVERSAL (Padrão Painel).

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Segurança
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['usuario_id'];
$ambiente_atual = $_SESSION['ambiente'] ?? 'indefinido';
$is_demo = ($ambiente_atual === 'demo');

// Lógica de Menu (Admin/Suporte)
$modo_suporte = isset($_SESSION['admin_original_id']);
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

$conn = $is_demo ? Database::getDemo() : Database::getProd();

// Carregar dados
$empresa = [];
$stmt = $conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ? LIMIT 1");
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $empresa = $res->fetch_assoc();
} else {
    $stmtIns = $conn->prepare("INSERT INTO DadosEmpresa (id_criador, Empresa, CNPJ) VALUES (?, 'Minha Empresa', '')");
    $stmtIns->bind_param('i', $id_usuario);
    $stmtIns->execute();
    header("Refresh:0");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Empresa | SGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-custom { background-color: #2c3e50; color: white; }
        .env-badge-demo { background-color: #ffc107; color: #000; font-weight: bold; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; }
        .env-badge-prod { background-color: #198754; color: #fff; font-weight: bold; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; }
        .support-banner { background-color: #dc3545; color: white; text-align: center; padding: 10px; font-weight: bold; position: sticky; top: 0; z-index: 1050; }
        .btn-upgrade { background-color: #25D366; color: white; font-weight: bold; border: none; animation: pulse 2s infinite; }
        .btn-upgrade:hover { background-color: #128C7E; color: white; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(37, 211, 102, 0); } 100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); } }
        .dropdown-menu-end { right: 0; left: auto; }
        .user-avatar { width: 32px; height: 32px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; }
    </style>
</head>
<body>

    <!-- TARJA SUPORTE -->
    <?php if($modo_suporte): ?>
        <div class="support-banner shadow">
            MODO SUPORTE: <?php echo strtoupper($nome_usuario); ?>
            <a href="painel.php?sair_suporte=1" class="btn btn-sm btn-light text-danger fw-bold ms-3">ENCERRAR</a>
        </div>
    <?php endif; ?>

    <!-- NAVBAR UNIVERSAL -->
    <nav class="navbar navbar-expand-lg navbar-custom px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="painel.php">
                <i class="bi bi-grid-fill me-2"></i>
                <div>SGT <span class="<?php echo $is_demo ? 'env-badge-demo' : 'env-badge-prod'; ?> ms-2"><?php echo $is_demo ? 'DEMO' : 'PRODUÇÃO'; ?></span></div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon text-white"><i class="bi bi-list"></i></span></button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-2"><a href="minha_empresa.php" class="btn btn-light btn-sm fw-bold text-dark"><i class="bi bi-gear-fill"></i> Empresa</a></li>
                    <li class="nav-item me-2"><a href="meus_clientes.php" class="btn btn-outline-light btn-sm fw-bold border-0"><i class="bi bi-people-fill"></i> Clientes</a></li>
                    <li class="nav-item me-2"><a href="relatorios.php" class="btn btn-outline-light btn-sm fw-bold border-0"><i class="bi bi-graph-up"></i> Relatórios</a></li>
                    <?php if($is_demo): ?><li class="nav-item mx-3"><a href="contratar.php" class="btn btn-upgrade btn-sm shadow-sm">CONTRATAR</a></li><?php endif; ?>
                    <?php if(!$is_demo && !$modo_suporte && isset($_SESSION['perfil']) && $_SESSION['perfil'] == 'admin'): ?>
                        <li class="nav-item me-2"><a href="admin_usuarios.php" class="btn btn-warning btn-sm fw-bold text-dark">Admin</a></li>
                        <li class="nav-item me-2"><a href="admin_parametros.php" class="btn btn-secondary btn-sm fw-bold text-white">Cadastros</a></li>
                    <?php endif; ?>
                    <?php if($_SESSION['usuario_id'] == 1 && !$modo_suporte): ?>
                        <li class="nav-item mx-2"><a href="admin_alternar.php" class="btn btn-sm fw-bold <?php echo $is_demo ? 'btn-success' : 'btn-warning'; ?>"><i class="bi bi-arrow-repeat"></i> Trocar</a></li>
                    <?php endif; ?>
                    
                    <!-- DROPDOWN USUÁRIO -->
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle text-white fw-bold d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <span class="user-avatar"><i class="bi bi-person-fill"></i></span> <?php echo htmlspecialchars($primeiro_nome); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="alterar_senha.php"><i class="bi bi-key-fill me-2 text-primary"></i> Alterar Senha</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <!-- COLUNA ESQUERDA: LOGO -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Logotipo da Empresa</div>
                    <div class="card-body text-center">
                        <?php $logo_atual = !empty($empresa['logo_caminho']) && file_exists(__DIR__ . '/' . $empresa['logo_caminho']) ? $empresa['logo_caminho'] : 'assets/img/sem_logo.png'; ?>
                        <div class="mb-3 border rounded p-2 bg-light d-flex align-items-center justify-content-center" style="height: 200px; overflow: hidden;">
                            <img src="<?php echo $logo_atual; ?>?t=<?php echo time(); ?>" alt="Logo" class="img-fluid" style="max-height: 100%;">
                        </div>
                        <form action="upload_logo.php" method="POST" enctype="multipart/form-data">
                            <input type="file" name="logo" class="form-control mb-3" accept="image/png, image/jpeg" required>
                            <button type="submit" class="btn btn-primary w-100 btn-sm"><i class="bi bi-upload me-2"></i>Enviar Logo</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- COLUNA DIREITA: DADOS -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between">
                        <span>Dados Cadastrais e Bancários</span>
                        <a href="painel.php" class="btn btn-sm btn-outline-secondary">Voltar ao Painel</a>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_GET['msg']) && $_GET['msg']=='sucesso'): ?><div class="alert alert-success py-2">Dados salvos com sucesso!</div><?php endif; ?>
                        
                        <form action="salvar_dados_empresa.php" method="POST">
                            <h6 class="text-primary mb-3 border-bottom pb-2">Identidade</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-8"><label class="form-label small fw-bold">Razão Social / Nome</label><input type="text" name="Empresa" class="form-control" value="<?php echo htmlspecialchars($empresa['Empresa']); ?>" required></div>
                                <div class="col-md-4"><label class="form-label small fw-bold">CNPJ / CPF</label><input type="text" name="CNPJ" class="form-control" value="<?php echo htmlspecialchars($empresa['CNPJ']); ?>"></div>
                            </div>
                            <div class="mb-3"><label class="form-label small fw-bold">Endereço</label><input type="text" name="Endereco" class="form-control" value="<?php echo htmlspecialchars($empresa['Endereco']); ?>"></div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-9"><label class="form-label small fw-bold">Cidade</label><input type="text" name="Cidade" class="form-control" value="<?php echo htmlspecialchars($empresa['Cidade']); ?>"></div>
                                <div class="col-md-3"><label class="form-label small fw-bold">UF</label><input type="text" name="Estado" class="form-control" value="<?php echo htmlspecialchars($empresa['Estado']); ?>" maxlength="2"></div>
                            </div>

                            <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">Contatos</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4"><label class="form-label small fw-bold">Telefone</label><input type="text" name="Telefone" class="form-control" value="<?php echo htmlspecialchars($empresa['Telefone']); ?>"></div>
                                <div class="col-md-4"><label class="form-label small fw-bold">Celular</label><input type="text" name="Celular" class="form-control" value="<?php echo htmlspecialchars($empresa['Celular']); ?>"></div>
                                <div class="col-md-4"><label class="form-label small fw-bold">WhatsApp</label><input type="text" name="Whatsapp" class="form-control" value="<?php echo htmlspecialchars($empresa['Whatsapp']); ?>"></div>
                            </div>

                            <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">Dados Bancários (Para Proposta)</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4"><label class="form-label small fw-bold">Banco</label><input type="text" name="Banco" class="form-control" value="<?php echo htmlspecialchars($empresa['Banco']); ?>"></div>
                                <div class="col-md-4"><label class="form-label small fw-bold">Agência</label><input type="text" name="Agencia" class="form-control" value="<?php echo htmlspecialchars($empresa['Agencia']); ?>"></div>
                                <div class="col-md-4"><label class="form-label small fw-bold">Conta</label><input type="text" name="Conta" class="form-control" value="<?php echo htmlspecialchars($empresa['Conta']); ?>"></div>
                                <div class="col-12"><label class="form-label small fw-bold">Chave PIX</label><input type="text" name="PIX" class="form-control" value="<?php echo htmlspecialchars($empresa['PIX']); ?>"></div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-2"></i>Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>