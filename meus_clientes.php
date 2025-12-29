<?php
// Nome do Arquivo: meus_clientes.php
// Função: Lista de Clientes com MENU UNIVERSAL.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit; }

$id_usuario = $_SESSION['usuario_id'];
$ambiente_atual = $_SESSION['ambiente'] ?? 'indefinido';
$is_demo = ($ambiente_atual === 'demo');
$modo_suporte = isset($_SESSION['admin_original_id']);
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

$conn = $is_demo ? Database::getDemo() : Database::getProd();

$clientes = [];
try {
    $sql = "SELECT * FROM Clientes WHERE id_criador = ? ORDER BY id_cliente DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $clientes[] = $row;
} catch (Exception $e) { }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Clientes | SGT</title>
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
        .avatar-initial { width: 35px; height: 35px; background-color: #e9ecef; color: #495057; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; }
    </style>
</head>
<body>

    <?php if($modo_suporte): ?>
        <div class="support-banner shadow">MODO SUPORTE: <?php echo strtoupper($nome_usuario); ?><a href="painel.php?sair_suporte=1" class="btn btn-sm btn-light text-danger fw-bold ms-3">ENCERRAR</a></div>
    <?php endif; ?>

    <nav class="navbar navbar-expand-lg navbar-custom px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="painel.php">
                <i class="bi bi-grid-fill me-2"></i>
                <div>SGT <span class="<?php echo $is_demo ? 'env-badge-demo' : 'env-badge-prod'; ?> ms-2"><?php echo $is_demo ? 'DEMO' : 'PRODUÇÃO'; ?></span></div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon text-white"><i class="bi bi-list"></i></span></button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-2"><a href="minha_empresa.php" class="btn btn-outline-light btn-sm fw-bold border-0"><i class="bi bi-gear-fill"></i> Empresa</a></li>
                    <li class="nav-item me-2"><a href="meus_clientes.php" class="btn btn-light btn-sm fw-bold text-dark"><i class="bi bi-people-fill"></i> Clientes</a></li>
                    <li class="nav-item me-2"><a href="relatorios.php" class="btn btn-outline-light btn-sm fw-bold border-0"><i class="bi bi-graph-up"></i> Relatórios</a></li>
                    <?php if($is_demo): ?><li class="nav-item mx-3"><a href="contratar.php" class="btn btn-upgrade btn-sm shadow-sm">CONTRATAR</a></li><?php endif; ?>
                    <?php if(!$is_demo && !$modo_suporte && isset($_SESSION['perfil']) && $_SESSION['perfil'] == 'admin'): ?>
                        <li class="nav-item me-2"><a href="admin_usuarios.php" class="btn btn-warning btn-sm fw-bold text-dark">Admin</a></li>
                        <li class="nav-item me-2"><a href="admin_parametros.php" class="btn btn-secondary btn-sm fw-bold text-white">Cadastros</a></li>
                    <?php endif; ?>
                    <?php if($_SESSION['usuario_id'] == 1 && !$modo_suporte): ?>
                        <li class="nav-item mx-2"><a href="admin_alternar.php" class="btn btn-sm fw-bold <?php echo $is_demo ? 'btn-success' : 'btn-warning'; ?>"><i class="bi bi-arrow-repeat"></i> Trocar</a></li>
                    <?php endif; ?>
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

    <div class="container pb-5 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="fw-bold text-dark mb-0">Carteira de Clientes</h2><p class="text-muted">Total: <strong><?php echo count($clientes); ?></strong> cadastrados</p></div>
            <a href="form_cliente.php" class="btn btn-primary shadow-sm"><i class="bi bi-person-plus-fill me-2"></i>Novo Cliente</a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?><div class="alert alert-success fade show mb-4">Operação realizada com sucesso!</div><?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'erro'): ?><div class="alert alert-danger fade show mb-4">Ocorreu um erro.</div><?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light"><tr><th class="ps-4">Nome</th><th>Empresa</th><th>Contatos</th><th class="text-end pe-4">Ações</th></tr></thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">Nenhum cliente encontrado. Cadastre o primeiro!</td></tr>
                            <?php else: ?>
                                <?php foreach ($clientes as $c): $inicial = !empty($c['nome_cliente']) ? strtoupper(substr($c['nome_cliente'], 0, 1)) : '?'; ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial me-3"><?php echo $inicial; ?></div>
                                            <div><div class="fw-bold"><?php echo htmlspecialchars($c['nome_cliente']); ?></div><small class="text-muted"><?php echo htmlspecialchars($c['email']); ?></small></div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($c['empresa'] ?? '-'); ?><br><small class="text-muted"><?php echo htmlspecialchars($c['cnpj_cpf']); ?></small></td>
                                    <td><?php if($c['celular']): ?><div class="small"><i class="bi bi-whatsapp text-success me-1"></i> <?php echo $c['celular']; ?></div><?php endif; ?></td>
                                    <td class="text-end pe-4"><a href="form_cliente.php?id=<?php echo $c['id_cliente']; ?>" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>