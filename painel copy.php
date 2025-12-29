<?php
//===== painel.php =====
// Nome do Arquivo: painel.php
// Função: Dashboard principal. Lista propostas, identifica Revisões e permite Download/Edição.

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

// Captura mensagens de erro/sucesso da URL
$msg = $_GET['msg'] ?? '';
$alerta = '';
if ($msg == 'sucesso_revisao_sem_docx') {
    $alerta = '<div class="alert alert-warning alert-dismissible fade show">Revisão salva no banco, mas o arquivo Word não foi gerado. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

// 2. Busca as Propostas do Usuário Logado
try {
    // Ordena por ID decrescente para que a REVISÃO mais recente apareça no topo
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

// Função para limpar nome do arquivo (igual ao salvar)
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
        
        .navbar-custom { background-color: #0e2130; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 800; letter-spacing: -0.5px; }
        
        /* Tabela Estilizada */
        .card-table { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; background: white; }
        .table thead th { background-color: #f8f9fa; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; padding: 15px; border-bottom: 2px solid #e9ecef; }
        .table tbody td { vertical-align: middle; padding: 15px; color: #343a40; font-size: 0.9rem; border-bottom: 1px solid #f1f3f5; }
        .table tbody tr:hover { background-color: #fcfcfc; }
        
        /* Badges */
        .badge-status { padding: 6px 12px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; }
        .bg-soft-primary { background-color: #e7f5ff; color: #1c7ed6; }
        
        /* Badge de Revisão (Destaque) */
        .badge-revisao { 
            background-color: #fff3cd; color: #d97706; 
            border: 1px solid #fcd34d; font-size: 0.7rem; 
            padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;
        }

        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: 0.2s; }
        .btn-action:hover { transform: scale(1.1); }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand text-white" href="#"><i class="bi bi-grid-fill me-2 text-warning"></i>SGT Manager</a>
            <div class="ms-auto">
                <span class="text-white-50 small me-3">Olá, <?= htmlspecialchars($nome_usuario) ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        
        <!-- ALERTAS -->
        <?= $alerta ?>

        <!-- HEADER DA PÁGINA -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Minhas Propostas</h2>
                <p class="text-muted mb-0">Histórico de orçamentos e revisões.</p>
            </div>
            <a href="criar_proposta.php" class="btn btn-success shadow rounded-pill px-4 py-2 fw-bold">
                <i class="bi bi-plus-lg me-2"></i>Nova Proposta
            </a>
        </div>

        <!-- TABELA -->
        <div class="card card-table">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Número / ID</th>
                            <th>Cliente</th>
                            <th>Valor Total</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                // Dados Formatados
                                $data = date('d/m/Y', strtotime($row['data_criacao']));
                                $valor = number_format($row['valor_final_proposta'], 2, ',', '.');
                                $cliente = $row['nome_cliente_salvo'] ? $row['nome_cliente_salvo'] : 'Cliente não salvo';
                                $numero = htmlspecialchars($row['numero_proposta']);
                                
                                // Verifica se é Revisão (contém -Rv)
                                $isRevisao = (strpos($numero, '-Rv') !== false);

                                // Lógica do Arquivo para Download
                                $empresaLimpa = limparStr($row['empresa_proponente_nome']);
                                $nomeArquivo = $empresaLimpa . '-' . $row['numero_proposta'] . '.docx';
                                $caminhoArquivo = __DIR__ . '/propostas_emitidas/' . $nomeArquivo;
                                $linkDownload = 'propostas_emitidas/' . $nomeArquivo;
                                $arquivoExiste = file_exists($caminhoArquivo);
                            ?>
                            <tr>
                                <td>
                                    <!-- NÚMERO DA PROPOSTA -->
                                    <div class="fw-bold text-dark" style="font-size: 1rem;">
                                        <?= $numero ?>
                                        <?php if($isRevisao): ?>
                                            <span class="badge-revisao ms-2"><i class="bi bi-clock-history me-1"></i>Revisão</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">ID Interno: <?= $row['id_proposta'] ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-secondary"><?= htmlspecialchars($cliente) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($row['cidade_obra']) ?> - <?= $row['estado_obra'] ?></div>
                                </td>
                                <td class="fw-bold text-success">R$ <?= $valor ?></td>
                                <td class="text-muted small"><?= $data ?></td>
                                <td>
                                    <span class="badge-status bg-soft-primary"><?= htmlspecialchars($row['status']) ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        
                                        <!-- BOTÃO DOWNLOAD (Só aparece se o arquivo existir) -->
                                        <?php if($arquivoExiste): ?>
                                            <a href="<?= $linkDownload ?>" class="btn btn-light btn-action text-primary border" title="Baixar Word" download>
                                                <i class="bi bi-file-earmark-word-fill"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="btn btn-light btn-action text-muted border disabled" title="Arquivo não encontrado"><i class="bi bi-file-earmark-x"></i></span>
                                        <?php endif; ?>

                                        <!-- BOTÃO EDITAR / GERAR REVISÃO -->
                                        <!-- Aponta para editar_proposta.php carregando este ID -->
                                        <a href="editar_proposta.php?id=<?= $row['id_proposta'] ?>" class="btn btn-light btn-action text-warning border" title="Criar Nova Revisão a partir desta">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder2-open fs-1 d-block mb-3 opacity-25"></i>
                                    Nenhuma proposta encontrada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>