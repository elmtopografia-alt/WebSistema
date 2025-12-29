<?php
// Nome do Arquivo: admin_usuarios.php
// Função: Gestão de Clientes com SENHA FORTE (Maiúscula, Minúscula, Número e Símbolo).

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Validação de Segurança
if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$conn = Database::getProd();
$erro = '';
$sucesso = '';

// --- LÓGICA DE RENOVAÇÃO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'renovar') {
    $id_user_renovar = intval($_POST['id_usuario_renovar']);
    $dias_extra = intval($_POST['dias_extra']);
    
    try {
        $stmtB = $conn->prepare("SELECT validade_acesso FROM Usuarios WHERE id_usuario = ?");
        $stmtB->bind_param('i', $id_user_renovar);
        $stmtB->execute();
        $atual = $stmtB->get_result()->fetch_assoc()['validade_acesso'];
        
        $data_base = new DateTime($atual);
        $hoje = new DateTime();
        
        if ($data_base < $hoje) $data_base = $hoje;
        
        $data_base->modify("+$dias_extra days");
        $nova_validade = $data_base->format('Y-m-d H:i:s');
        
        $stmtUp = $conn->prepare("UPDATE Usuarios SET validade_acesso = ? WHERE id_usuario = ?");
        $stmtUp->bind_param('si', $nova_validade, $id_user_renovar);
        $stmtUp->execute();
        
        $sucesso = "Acesso renovado com sucesso!";
    } catch (Exception $e) { $erro = "Erro: " . $e->getMessage(); }
}

// --- CADASTRO DE NOVO USUÁRIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar') {
    $nome    = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $senha   = trim($_POST['senha']);
    $empresa = trim($_POST['empresa']); 
    $plano   = $_POST['plano'];

    if (empty($nome) || empty($usuario) || empty($senha) || empty($empresa)) {
        $erro = "Preencha todos os campos.";
    } else {
        
        // --- VALIDAÇÃO DE SENHA ULTRA FORTE ---
        if (strlen($senha) < 8 || 
            !preg_match("/[A-Z]/", $senha) ||  // Pelo menos 1 Maiúscula
            !preg_match("/[a-z]/", $senha) ||  // Pelo menos 1 Minúscula
            !preg_match("/[0-9]/", $senha) ||  // Pelo menos 1 Número
            !preg_match("/[\W]/", $senha))     // Pelo menos 1 Símbolo
        {
            $erro = "Senha fraca! Obrigatório: 8 dígitos, 1 Maiúscula, 1 Minúscula, 1 Número e 1 Símbolo (ex: @, #).";
        } else {
            
            $conn->begin_transaction();
            try {
                $stmtCheck = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
                $stmtCheck->bind_param('s', $usuario);
                $stmtCheck->execute();
                if ($stmtCheck->get_result()->num_rows > 0) throw new Exception("Login já existe.");

                $dias = 30;
                if ($plano == 'trimestral') $dias = 90;
                if ($plano == 'semestral')  $dias = 180;
                if ($plano == 'anual')      $dias = 365;
                
                $validade = date('Y-m-d H:i:s', strtotime("+$dias days"));
                
                $stmtUser = $conn->prepare("INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, tipo_perfil, validade_acesso, data_cadastro) VALUES (?, ?, ?, 1, 'producao', 'cliente', ?, NOW())");
                $stmtUser->bind_param('ssss', $usuario, $senha, $nome, $validade);
                
                if (!$stmtUser->execute()) throw new Exception("Erro ao criar usuário.");
                $id_novo_usuario = $conn->insert_id;

                $stmtEmp = $conn->prepare("INSERT INTO DadosEmpresa (id_criador, Empresa, Cidade, Estado, CNPJ) VALUES (?, ?, 'Cidade a Definir', 'UF', '00.000.000/0000-00')");
                $stmtEmp->bind_param('is', $id_novo_usuario, $empresa);
                
                if (!$stmtEmp->execute()) throw new Exception("Erro ao criar empresa vinculada.");

                $conn->commit();
                $sucesso = "Cliente cadastrado com sucesso!";
                
            } catch (Exception $e) {
                $conn->rollback();
                $erro = $e->getMessage();
            }
        }
    }
}

// Lista
$lista_usuarios = [];
try {
    $sqlList = "SELECT u.id_usuario, u.nome_completo, u.usuario, u.senha, u.validade_acesso, d.Empresa 
                FROM Usuarios u LEFT JOIN DadosEmpresa d ON u.id_usuario = d.id_criador 
                WHERE u.ambiente = 'producao' AND u.tipo_perfil != 'admin' ORDER BY u.validade_acesso ASC";
    $resList = $conn->query($sqlList);
    while ($row = $resList->fetch_assoc()) $lista_usuarios[] = $row;
} catch (Exception $e) { }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Clientes | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .status-dot { height: 12px; width: 12px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .bg-vencido { background-color: #dc3545; box-shadow: 0 0 5px #dc3545; }
        .bg-alerta { background-color: #ffc107; box-shadow: 0 0 5px #ffc107; }
        .bg-ok { background-color: #198754; }
        .card-dash { border-left: 5px solid #0d6efd; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="painel.php"><i class="bi bi-arrow-left me-2"></i>Voltar ao Painel</a>
            <span class="navbar-text text-white">Painel Financeiro Admin</span>
        </div>
    </nav>

    <div class="container pb-5">

        <?php if($erro): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
        <?php if($sucesso): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>

        <div class="row">
            <!-- CADASTRO -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-person-plus-fill me-2"></i>Novo Cliente
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="acao" value="criar">
                            <div class="mb-2">
                                <label class="small text-muted">Plano Inicial</label>
                                <select name="plano" class="form-select form-select-sm fw-bold">
                                    <option value="mensal">Mensal (30 dias)</option>
                                    <option value="trimestral">Trimestral (90 dias)</option>
                                    <option value="semestral">Semestral (180 dias)</option>
                                    <option value="anual">Anual (365 dias)</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="small text-muted">Empresa</label>
                                <input type="text" name="empresa" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-muted">Responsável</label>
                                <input type="text" name="nome" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="small text-muted">Login</label>
                                <input type="text" name="usuario" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted">Senha Inicial</label>
                                <input type="text" name="senha" class="form-control form-control-sm" required placeholder="Ex: @Mudar123">
                                <div class="form-text text-danger small">Mín 8 chars, 1 Maiúscula, 1 Minúscula, 1 Número, 1 Símbolo.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-sm fw-bold">Cadastrar</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TABELA SEMÁFORO -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 card-dash">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span>Monitoramento de Assinaturas</span>
                        <span class="badge bg-light text-dark border">Total: <?php echo count($lista_usuarios); ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Status</th>
                                        <th>Cliente</th>
                                        <th>Vencimento</th>
                                        <th class="text-end pe-3">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lista_usuarios as $u): 
                                        $hoje = new DateTime();
                                        $val = new DateTime($u['validade_acesso']);
                                        $intervalo = $hoje->diff($val);
                                        $dias_restantes = $intervalo->days;
                                        $invert = $intervalo->invert;

                                        if ($invert == 1) {
                                            $status = '<span class="status-dot bg-vencido"></span> <span class="text-danger fw-bold small">VENCIDO</span>';
                                            $texto_dias = "Venceu há {$dias_restantes} dias";
                                        } elseif ($dias_restantes <= 10) {
                                            $status = '<span class="status-dot bg-alerta"></span> <span class="text-warning fw-bold small" style="color:#d39e00!important">RENOVAR</span>';
                                            $texto_dias = "Vence em <strong>{$dias_restantes} dias</strong>";
                                        } else {
                                            $status = '<span class="status-dot bg-ok"></span> <span class="text-success fw-bold small">ATIVO</span>';
                                            $texto_dias = "{$dias_restantes} dias restantes";
                                        }
                                    ?>
                                    <tr>
                                        <td class="ps-3"><?php echo $status; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($u['Empresa']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($u['usuario']); ?></div>
                                        </td>
                                        <td class="small">
                                            <div><?php echo date('d/m/Y', strtotime($u['validade_acesso'])); ?></div>
                                            <div class="text-muted" style="font-size: 0.8rem;"><?php echo $texto_dias; ?></div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalRenovar<?php echo $u['id_usuario']; ?>" title="Renovar Assinatura"><i class="bi bi-cash-coin"></i></button>
                                            <a href="admin_acessar_cliente.php?id=<?php echo $u['id_usuario']; ?>" class="btn btn-sm btn-outline-dark" onclick="return confirm('Entrar como este cliente?');" title="Acessar Painel"><i class="bi bi-mask"></i></a>

                                            <!-- Modal Renovar -->
                                            <div class="modal fade" id="modalRenovar<?php echo $u['id_usuario']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Renovar: <?php echo htmlspecialchars($u['Empresa']); ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <input type="hidden" name="acao" value="renovar">
                                                                <input type="hidden" name="id_usuario_renovar" value="<?php echo $u['id_usuario']; ?>">
                                                                <p>O cliente realizou o pagamento? Selecione o período para adicionar:</p>
                                                                <div class="d-grid gap-2">
                                                                    <label class="btn btn-outline-primary text-start"><input type="radio" name="dias_extra" value="30" checked> + 30 Dias (Mensal)</label>
                                                                    <label class="btn btn-outline-primary text-start"><input type="radio" name="dias_extra" value="90"> + 90 Dias (Trimestral)</label>
                                                                    <label class="btn btn-outline-primary text-start"><input type="radio" name="dias_extra" value="180"> + 180 Dias (Semestral)</label>
                                                                    <label class="btn btn-outline-primary text-start"><input type="radio" name="dias_extra" value="365"> + 365 Dias (Anual)</label>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-success fw-bold">Confirmar Renovação</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>