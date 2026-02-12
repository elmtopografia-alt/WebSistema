<?php
// Nome do Arquivo: meus_clientes.php
// Função: Listagem completa dos clientes do usuário logado.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Validação de Acesso
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$is_demo = ($_SESSION['ambiente'] === 'demo');
$conn = $is_demo ? Database::getDemo() : Database::getProd();

// 2. Busca Clientes
$clientes = [];
try {
    // Ordena do mais recente para o mais antigo
    $sql = "SELECT * FROM Clientes WHERE id_criador = ? ORDER BY id_cliente DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
} catch (Exception $e) {
    $erro = "Erro ao carregar clientes: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Clientes | CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .table-custom { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .avatar-initial { width: 35px; height: 35px; background-color: #e9ecef; color: #495057; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; }
    </style>
</head>
<body>

    <!-- Navbar Simplificada -->
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-arrow-left me-2"></i>Voltar ao Painel</a>
            <span class="navbar-text text-white">Gestão de Clientes</span>
        </div>
    </nav>

    <div class="container pb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-0">Carteira de Clientes</h2>
                <p class="text-muted">Total: <strong><?php echo count($clientes); ?></strong> cadastrados</p>
            </div>
            <a href="form_cliente.php" class="btn btn-primary shadow-sm">
                <i class="bi bi-person-plus-fill me-2"></i>Novo Cliente
            </a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
            <div class="alert alert-success fade show mb-4">Operação realizada com sucesso!</div>
        <?php endif; ?>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'erro'): ?>
            <div class="alert alert-danger fade show mb-4">Ocorreu um erro na operação.</div>
        <?php endif; ?>

        <div class="table-custom border">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nome</th>
                        <th>Empresa</th>
                        <th>Contatos</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-3 opacity-25"></i>
                                Nenhum cliente encontrado. Cadastre o primeiro!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $c): 
                            // Pega a inicial do nome para o avatar visual
                            $inicial = !empty($c['nome_cliente']) ? strtoupper(substr($c['nome_cliente'], 0, 1)) : '?';
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-initial me-3"><?php echo $inicial; ?></div>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($c['nome_cliente']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($c['email']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($c['empresa'] ?? '-'); ?>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($c['cnpj_cpf']); ?></small>
                            </td>
                            <td>
                                <?php if($c['celular']): ?>
                                    <div class="small"><i class="bi bi-whatsapp text-success me-1"></i> <?php echo $c['celular']; ?></div>
                                <?php endif; ?>
                                <?php if($c['telefone']): ?>
                                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i> <?php echo $c['telefone']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="form_cliente.php?id=<?php echo $c['id_cliente']; ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>