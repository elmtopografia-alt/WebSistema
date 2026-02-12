<?php
// Nome do Arquivo e: admin_usuarios.php
// Função: Gestão de Clientes. Atualização: Inclui Checklist Visual de Senha Forte no formulário.

session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') { header("Location: login.php"); exit; }

$conn = Database::getProd();
$erro = '';
$sucesso = '';

// Renovar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'renovar') {
    $id = intval($_POST['id_usuario_renovar']);
    $dias = intval($_POST['dias_extra']);
    try {
        $stmt = $conn->prepare("SELECT validade_acesso FROM Usuarios WHERE id_usuario = ?");
        $stmt->bind_param('i', $id); $stmt->execute(); $atual = $stmt->get_result()->fetch_assoc()['validade_acesso'];
        $base = new DateTime($atual); $hoje = new DateTime();
        if ($base < $hoje) $base = $hoje;
        $base->modify("+$dias days");
        $nova = $base->format('Y-m-d H:i:s');
        $up = $conn->prepare("UPDATE Usuarios SET validade_acesso = ? WHERE id_usuario = ?");
        $up->bind_param('si', $nova, $id); $up->execute();
        $sucesso = "Renovado!";
    } catch(Exception $e) { $erro = "Erro renovação."; }
}

// CRIAR CLIENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar') {
    $nome    = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $senha   = trim($_POST['senha']);
    $empresa = trim($_POST['empresa']); 
    $plano   = $_POST['plano'];

    if (empty($nome) || empty($usuario) || empty($senha) || empty($empresa)) {
        $erro = "Preencha tudo.";
    } elseif (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
        $erro = "O Login deve ser um e-mail válido.";
    } elseif (strlen($senha) < 8 || !preg_match("/[A-Z]/", $senha) || !preg_match("/[a-z]/", $senha) || !preg_match("/[0-9]/", $senha) || !preg_match("/[\W]/", $senha)) {
        $erro = "Senha fraca! Siga todas as regras do checklist.";
    } else {
        $conn->begin_transaction();
        try {
            $check = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
            $check->bind_param('s', $usuario); $check->execute();
            if ($check->get_result()->num_rows > 0) throw new Exception("E-mail já cadastrado.");

            $dias = 30;
            if ($plano == 'trimestral') $dias = 90;
            if ($plano == 'semestral')  $dias = 180;
            if ($plano == 'anual')      $dias = 365;
            $validade = date('Y-m-d H:i:s', strtotime("+$dias days"));
            
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            $ins = $conn->prepare("INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, tipo_perfil, validade_acesso, data_cadastro) VALUES (?, ?, ?, 1, 'producao', 'cliente', ?, NOW())");
            $ins->bind_param('ssss', $usuario, $senha_hash, $nome, $validade);
            
            if (!$ins->execute()) throw new Exception("Erro SQL Usuario.");
            $id_novo = $conn->insert_id;

            $empIn = $conn->prepare("INSERT INTO DadosEmpresa (id_criador, Empresa, Cidade, Estado, CNPJ) VALUES (?, ?, 'A Definir', 'UF', '')");
            $empIn->bind_param('is', $id_novo, $empresa);
            if (!$empIn->execute()) throw new Exception("Erro SQL Empresa.");

            $conn->commit();
            $sucesso = "Cliente criado com sucesso!";
            
        } catch (Exception $e) { $conn->rollback(); $erro = $e->getMessage(); }
    }
}

// Listagem
$lista_usuarios = [];
try {
    $q = "SELECT * FROM Usuarios u LEFT JOIN DadosEmpresa d ON u.id_usuario = d.id_criador WHERE u.ambiente='producao' AND u.tipo_perfil!='admin' ORDER BY u.validade_acesso ASC";
    $r = $conn->query($q);
    while($rw = $r->fetch_assoc()) $lista_usuarios[] = $rw;
} catch(Exception $e){}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .status-dot{height:10px;width:10px;border-radius:50%;display:inline-block;margin-right:5px}.bg-vencido{background:#dc3545}.bg-alerta{background:#ffc107}.bg-ok{background:#198754}
        
        /* Estilo do Checklist de Senha */
        .pwd-rules { font-size: 0.75rem; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px; display: none; }
        .rule-item { margin-bottom: 2px; color: #aaa; transition: 0.3s; display: flex; align-items: center; gap: 5px; }
        .rule-item.valid { color: #198754; font-weight: bold; }
        .rule-item i { font-size: 0.8rem; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4"><div class="container"><a class="navbar-brand" href="painel.php"><i class="bi bi-arrow-left"></i> Painel</a><span class="text-white">Admin Financeiro</span></div></nav>
    <div class="container pb-5">
        <?php if($erro): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
        <?php if($sucesso): ?><div class="alert alert-success"><?php echo $sucesso; ?></div><?php endif; ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">Novo Cliente</div>
                    <div class="card-body">
                        <form method="POST" id="formCriar" autocomplete="off">
                            <input type="hidden" name="acao" value="criar">
                            <div class="mb-2"><label class="small">Plano</label><select name="plano" class="form-select form-select-sm fw-bold"><option value="mensal">Mensal</option><option value="anual">Anual</option></select></div>
                            <div class="mb-2"><label class="small">Empresa</label><input type="text" name="empresa" class="form-control form-control-sm" required></div>
                            <div class="mb-2"><label class="small">Nome Resp.</label><input type="text" name="nome" class="form-control form-control-sm" required></div>
                            <div class="mb-2"><label class="small">E-mail de Login</label><input type="email" name="usuario" class="form-control form-control-sm" required placeholder="cliente@email.com" autocomplete="off"></div>
                            
                            <div class="mb-3">
                                <label class="small fw-bold text-primary">Senha Inicial</label>
                                <input type="text" name="senha" id="senhaInput" class="form-control form-control-sm" required placeholder="Crie uma senha forte..." autocomplete="off">
                                
                                <!-- CHECKLIST VISUAL -->
                                <div class="pwd-rules" id="rulesBox">
                                    <div class="rule-item" id="r-len"><i class="bi bi-circle"></i> 8 Caracteres</div>
                                    <div class="rule-item" id="r-up"><i class="bi bi-circle"></i> Maiúscula (A-Z)</div>
                                    <div class="rule-item" id="r-low"><i class="bi bi-circle"></i> Minúscula (a-z)</div>
                                    <div class="rule-item" id="r-num"><i class="bi bi-circle"></i> Número (0-9)</div>
                                    <div class="rule-item" id="r-sym"><i class="bi bi-circle"></i> Símbolo (@#$%)</div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-sm fw-bold" id="btnSubmit">Cadastrar</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                         <table class="table table-striped mb-0 align-middle">
                            <thead class="table-light"><tr><th class="ps-3">Status</th><th>Cliente</th><th>Vencimento</th><th class="text-end pe-3">Ações</th></tr></thead>
                            <tbody>
                                <?php foreach ($lista_usuarios as $u): 
                                    $hoje = new DateTime(); $val = new DateTime($u['validade_acesso']); $invert = $hoje->diff($val)->invert;
                                    $st = ($invert) ? '<span class="status-dot bg-vencido"></span>' : '<span class="status-dot bg-ok"></span>';
                                ?>
                                <tr>
                                    <td class="ps-3"><?php echo $st; ?></td>
                                    <td><div class="fw-bold"><?php echo htmlspecialchars($u['Empresa']); ?></div><small class="text-muted"><?php echo htmlspecialchars($u['usuario']); ?></small></td>
                                    <td class="small"><?php echo date('d/m/Y', strtotime($u['validade_acesso'])); ?></td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalRenovar<?php echo $u['id_usuario']; ?>"><i class="bi bi-cash-coin"></i></button>
                                        <a href="admin_acessar_cliente.php?id=<?php echo $u['id_usuario']; ?>" class="btn btn-sm btn-outline-dark" onclick="return confirm('Acessar painel?');"><i class="bi bi-mask"></i></a>
                                        <!-- Modal Renovar (Simplificado para caber) -->
                                        <div class="modal fade" id="modalRenovar<?php echo $u['id_usuario']; ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-body"><input type="hidden" name="acao" value="renovar"><input type="hidden" name="id_usuario_renovar" value="<?php echo $u['id_usuario']; ?>"><label>Adicionar dias:</label><select name="dias_extra" class="form-select"><option value="30">30 Dias</option><option value="365">1 Ano</option></select></div><div class="modal-footer"><button class="btn btn-success">Confirmar</button></div></form></div></div></div>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // SCRIPT DE VALIDAÇÃO VISUAL
        const pass = document.getElementById('senhaInput');
        const box = document.getElementById('rulesBox');
        const btn = document.getElementById('btnSubmit');

        pass.addEventListener('focus', () => box.style.display = 'block');
        pass.addEventListener('blur', () => { if(pass.value=='') box.style.display = 'none'; });

        pass.addEventListener('input', function() {
            const v = pass.value;
            let score = 0;
            
            // Regras
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
                    score++;
                } else {
                    el.classList.remove('valid');
                    el.querySelector('i').className = 'bi bi-circle';
                }
            });

            // Bloqueia ou Libera botão
            /* if(score < 5) btn.disabled = true; else btn.disabled = false; */
        });
        
        // Bloqueio Final no Submit
        document.getElementById('formCriar').addEventListener('submit', function(e) {
            const v = pass.value;
            if (v.length < 8 || !/[A-Z]/.test(v) || !/[a-z]/.test(v) || !/[0-9]/.test(v) || !/[\W_]/.test(v)) {
                e.preventDefault();
                alert('A senha criada para o cliente é fraca.\nPor favor, siga as regras do checklist.');
                pass.focus();
            }
        });
    </script>
</body>
</html>