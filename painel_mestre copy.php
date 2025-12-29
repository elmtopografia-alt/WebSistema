<?php
// ARQUIVO: painel_mestre.php
// FUNÇÃO: Painel de Controle Total para o Super Admin

session_start();
require_once 'config.php';

// 1. SEGURANÇA MÁXIMA: Apenas o usuário ID=1 pode acessar.
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_id'] != 1) {
    die("<div style='padding:50px;text-align:center;font-family:sans-serif;'><h1>🚫 Acesso Negado</h1><p>Este painel é exclusivo para a administração do sistema.</p><a href='login.php'>Voltar</a></div>");
}

$msg = "";
$novos_dados = null;

// 2. PROCESSAMENTO DE AÇÕES (CRIAR, DELETAR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['acao'])) {
    
    $acao = $_POST['acao'] ?? $_GET['acao'];
    $ambiente_alvo = $_POST['ambiente_alvo'] ?? $_GET['amb'];

    // Define conexão baseada na escolha
    if ($ambiente_alvo === 'demo') {
        $host = DB_DEMO_HOST; $user = DB_DEMO_USER; $pass = DB_DEMO_PASS; $name = DB_DEMO_NAME;
        $cor = "primary"; $label_amb = "DEMONSTRAÇÃO";
    } else {
        $host = DB_PROD_HOST; $user = DB_PROD_USER; $pass = DB_PROD_PASS; $name = DB_PROD_NAME;
        $cor = "success"; $label_amb = "PRODUÇÃO";
    }
    $conn_alvo = new mysqli($host, $user, $pass, $name);
    if ($conn_alvo->connect_error) { die("Erro de conexão com $label_amb"); }

    // --- AÇÃO: CRIAR USUÁRIO ---
    if ($acao === 'criar') {
        $nome = trim($_POST['nome']); $email = trim($_POST['email']); $senha = trim($_POST['senha']);
        $validade = ($ambiente_alvo === 'demo') ? date('Y-m-d H:i:s', strtotime('+5 days')) : null;
        
        $check = $conn_alvo->prepare("SELECT id_usuario FROM Usuarios WHERE usuario = ?");
        $check->bind_param('s', $email); $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $msg = "<div class='alert alert-warning'>⚠️ E-mail <strong>$email</strong> já existe em <strong>$label_amb</strong>.</div>";
        } else {
            $sql = "INSERT INTO Usuarios (usuario, senha, nome_completo, setup_concluido, ambiente, validade_acesso, tipo_perfil) VALUES (?, ?, ?, 1, ?, ?, 'admin')";
            $stmt = $conn_alvo->prepare($sql);
            $stmt->bind_param('sssss', $email, $senha, $nome, $ambiente_alvo, $validade);
            
            if ($stmt->execute()) {
                $novo_id = $conn_alvo->insert_id;
                $conn_alvo->query("INSERT INTO DadosEmpresa (id_criador, Empresa, CNPJ) VALUES ($novo_id, 'Empresa de $nome', '')");
                $novos_dados = ['ambiente'=>$label_amb, 'url'=>($ambiente_alvo=='demo'?'login_demo.php':'login_prod.php'), 'login'=>$email, 'senha'=>$senha, 'cor'=>$cor];
            } else { $msg = "<div class='alert alert-danger'>Erro: ".$stmt->error."</div>"; }
        }
    }

    // --- AÇÃO: DELETAR USUÁRIO ---
    if ($acao === 'deletar') {
        $id_deletar = intval($_GET['id']);
        if ($id_deletar == 1) {
            $msg = "<div class='alert alert-danger'>🚫 Você não pode deletar o Super Admin (ID 1).</div>";
        } else {
            // Cascade delete (se não houver no DB)
            $conn_alvo->query("DELETE FROM Propostas WHERE id_criador = $id_deletar");
            $conn_alvo->query("DELETE FROM Clientes WHERE id_criador = $id_deletar");
            $conn_alvo->query("DELETE FROM DadosEmpresa WHERE id_criador = $id_deletar");
            
            $stmt = $conn_alvo->prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
            $stmt->bind_param('i', $id_deletar);
            if($stmt->execute()) {
                $msg = "<div class='alert alert-success'>✅ Usuário ID $id_deletar deletado de <strong>$label_amb</strong>.</div>";
            }
        }
    }
    $conn_alvo->close();
}

// 3. LISTAGEM DE DADOS
$conn_demo = new mysqli(DB_DEMO_HOST, DB_DEMO_USER, DB_DEMO_PASS, DB_DEMO_NAME);
$res_demo = $conn_demo->query("SELECT id_usuario, usuario, nome_completo, validade_acesso FROM Usuarios ORDER BY id_usuario DESC");

$conn_prod = new mysqli(DB_PROD_HOST, DB_PROD_USER, DB_PROD_PASS, DB_PROD_NAME);
$res_prod = $conn_prod->query("SELECT id_usuario, usuario, nome_completo FROM Usuarios ORDER BY id_usuario DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Painel Mestre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
    <nav class="navbar navbar-dark bg-black mb-4"><div class="container"><span class="navbar-brand fw-bold"><i class="fa-solid fa-user-secret me-2"></i>PAINEL MESTRE</span><a href="logout.php" class="text-white small">Sair</a></div></nav>

    <div class="container">
        <?= $msg ?>
        <?php if ($novos_dados): ?>
        <div class="card mb-4 border-<?= $novos_dados['cor'] ?>"><div class="card-header bg-<?= $novos_dados['cor'] ?> text-white fw-bold">NOVO USUÁRIO GERADO</div><div class="card-body bg-light text-dark"><p>Copie e envie para o cliente:</p><textarea class="form-control mb-2" rows="5" readonly>Olá! Seu acesso ao sistema ELM (Ambiente <?= $novos_dados['ambiente'] ?>) foi liberado.&#13;&#10;&#13;&#10;🔗 Link: https://elmtopografia.com.br/Orcamento/<?= $novos_dados['url'] ?>&#13;&#10;&#13;&#10;👤 Login: <?= $novos_dados['login'] ?>&#13;&#10;🔑 Senha: <?= $novos_dados['senha'] ?></textarea><button class="btn btn-dark btn-sm" onclick="navigator.clipboard.writeText(this.previousElementSibling.value);this.innerText='Copiado!';"><i class="fa-solid fa-copy"></i> Copiar</button></div></div>
        <?php endif; ?>

        <div class="card text-dark shadow mb-4"><div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fa-solid fa-user-plus me-2"></i>Gerar Novo Acesso</h5></div><div class="card-body p-4"><form method="POST"><input type="hidden" name="acao" value="criar"><div class="row g-3"><div class="col-12"><label class="form-label fw-bold">Ambiente</label><div class="btn-group w-100"><input type="radio" class="btn-check" name="ambiente_alvo" id="optProd" value="producao" checked><label class="btn btn-outline-success" for="optProd"><i class="fa-solid fa-lock me-2"></i>PRODUÇÃO</label><input type="radio" class="btn-check" name="ambiente_alvo" id="optDemo" value="demo"><label class="btn btn-outline-primary" for="optDemo"><i class="fa-solid fa-rocket me-2"></i>DEMO</label></div></div><div class="col-md-6"><label>Nome / Empresa</label><input type="text" name="nome" class="form-control" required></div><div class="col-md-6"><label>E-mail (Login)</label><input type="email" name="email" class="form-control" required></div><div class="col-md-12"><label>Senha</label><div class="input-group"><input type="text" name="senha" id="senhaInput" class="form-control fw-bold" required><button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('senhaInput').value=Math.floor(100000+Math.random()*900000);"><i class="fa-solid fa-dice"></i> Gerar</button></div></div></div><div class="d-grid mt-4"><button type="submit" class="btn btn-dark btn-lg">CRIAR ACESSO</button></div></form></div></div>

        <!-- LISTAGEM -->
        <div class="card text-dark shadow"><div class="card-header bg-white"><ul class="nav nav-tabs card-header-tabs"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-prod">Produção (<?= $res_prod->num_rows ?>)</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-demo">Demo (<?= $res_demo->num_rows ?>)</button></li></ul></div><div class="card-body"><div class="tab-content"><div class="tab-pane fade show active" id="tab-prod"><?= renderizarTabela($res_prod, 'producao') ?></div><div class="tab-pane fade" id="tab-demo"><?= renderizarTabela($res_demo, 'demo') ?></div></div></div></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
function renderizarTabela($resultado, $ambiente) {
    if ($resultado->num_rows == 0) return "<p class='text-center text-muted'>Nenhum usuário neste ambiente.</p>";
    $html = "<div class='table-responsive'><table class='table table-sm table-striped table-hover'><thead><tr><th>ID</th><th>Email/Login</th><th>Nome</th>";
    if ($ambiente == 'demo') $html .= "<th>Validade</th>";
    $html .= "<th>Ação</th></tr></thead><tbody>";
    while ($row = $resultado->fetch_assoc()) {
        $html .= "<tr><td>{$row['id_usuario']}</td><td>{$row['usuario']}</td><td>{$row['nome_completo']}</td>";
        if ($ambiente == 'demo') {
            $validade = new DateTime($row['validade_acesso']);
            $hoje = new DateTime();
            $cor = ($validade < $hoje) ? 'text-danger' : 'text-success';
            $html .= "<td class='$cor'>".$validade->format('d/m/Y')."</td>";
        }
        $html .= "<td><a href='?acao=deletar&id={$row['id_usuario']}&amb=$ambiente' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Deletar este usuário?\")'><i class='fa-solid fa-trash'></i></a></td></tr>";
    }
    $html .= "</tbody></table></div>";
    return $html;
}
?>```

