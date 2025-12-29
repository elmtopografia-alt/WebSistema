<?php
//===== painel.php =====
// Nome do Arquivo: painel.php
// Função: Dashboard principal. Lista propostas e conecta ao editar_proposta.php

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. Verificação de Segurança
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// 2. Busca as Propostas do Usuário Logado
try {
    // Busca propostas ordenadas da mais recente para a mais antiga
    $sql = "SELECT p.*, c.nome_cliente 
            FROM Propostas p 
            LEFT JOIN Clientes c ON p.id_cliente = c.id_cliente 
            WHERE p.id_criador = ? 
            ORDER BY p.id_proposta DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
} catch (Exception $e) {
    die("Erro ao carregar painel: " . $e->getMessage());
}

// Helper para limpar string (para o link de download)
function limparStr($string) { return preg_replace('/[^a-zA-Z0-9]/', '', $string); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Controle | SGT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body { background-color: #f4f6f8; font-family: 'Inter', sans-serif; }
        
        /* Navbar */
        .navbar-custom { background-color: #0e2130; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 800; letter-spacing: -0.5px; }
        
        /* Cards de Resumo */
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 1.2rem; }
        
        /* Tabela */
        .card-table { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .table thead th { background-color: #f8f9fa; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; padding: 15px; border-bottom: 2px solid #e9ecef; }
        .table tbody td { vertical-align: middle; padding: 15px; color: #343a40; font-size: 0.9rem; border-bottom: 1px solid #f1f3f5; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background-color: #fcfcfc; }
        
        /* Status Badges */
        .badge-status { padding: 6px 12px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px; }
        .bg-soft-warning { background-color: #fff8e1; color: #b76e00; }
        .bg-soft-success { background-color: #e6fcf5; color: #0ca678; }
        .bg-soft-primary { background-color: #e7f5ff; color: #1c7ed6; }
        
        /* Botões de Ação */
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: 0.2s; }
        .btn-action:hover { transform: scale(1.1); }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand text-white" href="#"><i class="bi bi-grid-fill me-2 text-success"></i>SGT <span class="fw-light opacity-50">Manager</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3"><span class="text-white-50 small">Olá, <strong class="text-white"><?= htmlspecialchars($nome_usuario) ?></strong></span></li>
                    <li class="nav-item"><a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        
        <!-- HEADER DA PÁGINA -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Minhas Propostas</h2>
                <p class="text-muted mb-0">Gerencie seus orçamentos e revisões.</p>
            </div>
            <a href="criar_proposta.php" class="btn btn-success shadow-lg rounded-pill px-4 py-2 fw-bold">
                <i class="bi bi-plus-lg me-2"></i>Nova Proposta
            </a>
        </div>

        <!-- LISTA DE PROPOSTAS -->
        <div class="card card-table">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Cliente / Obra</th>
                            <th>Valor Total</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                // Formatação de dados
                                $data = date('d/m/Y', strtotime($row['data_criacao']));
                                $valor = number_format($row['valor_final_proposta'], 2, ',', '.');
                                $cliente = $row['nome_cliente_salvo'] ? $row['nome_cliente_salvo'] : 'Cliente não salvo';
                                $obra = $row['cidade_obra'] ? $row['cidade_obra'] . '-' . $row['estado_obra'] : '';
                                
                                // Lógica do Arquivo
                                $empresaLimpa = limparStr($row['empresa_proponente_nome']);
                                $nomeArquivo = $empresaLimpa . '-' . $row['numero_proposta'] . '.docx';
                                $caminhoArquivo = 'propostas_emitidas/' . $nomeArquivo;
                                $arquivoExiste = file_exists($caminhoArquivo);
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($row['numero_proposta']) ?></span>
                                    <?php if(strpos($row['numero_proposta'], '-Rv') !== false): ?>
                                        <span class="badge bg-info text-dark ms-1" style="font-size: 0.6rem;">REVISÃO</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($cliente) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($obra) ?></div>
                                </td>
                                <td class="fw-bold text-success">R$ <?= $valor ?></td>
                                <td class="text-muted small"><?= $data ?></td>
                                <td>
                                    <span class="badge-status bg-soft-primary"><?= htmlspecialchars($row['status']) ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        
                                        <!-- BOTÃO DE DOWNLOAD (Se arquivo existir) -->
                                        <?php if($arquivoExiste): ?>
                                            <a href="propostas_emitidas/<?= $nomeArquivo ?>" class="btn btn-light btn-action text-primary border" title="Baixar Word" download>
                                                <i class="bi bi-file-earmark-word-fill"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- BOTÃO DE EDITAR/REVISAR (LINKANDO CORRETAMENTE AGORA) -->
                                        <!-- Aponta para editar_proposta.php com o ID da linha -->
                                        <a href="editar_proposta.php?id=<?= $row['id_proposta'] ?>" class="btn btn-light btn-action text-warning border" title="Criar Revisão / Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                    Nenhuma proposta encontrada. Comece criando uma nova!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>