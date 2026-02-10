<?php
// Nome do Arquivo: final.php
// Função: Dashboard de Entrega de Documentos (Pós-Geração).
// Substitui a antiga "proposta_sucesso.php" e a antiga Landing Page de Venda.

session_start();
require_once 'config.php';
require_once 'db.php';

// 1. SEGURANÇA: Valida Sessão
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// 2. VALIDAÇÃO DOS DADOS
$id_proposta = isset($_GET['id']) ? intval($_GET['id']) : 0;
$arquivo     = isset($_GET['arquivo']) ? $_GET['arquivo'] : '';

// Verifica se o arquivo existe
$caminhoRelativo = 'propostas_emitidas/' . basename($arquivo);
$caminhoAbsoluto = __DIR__ . '/' . $caminhoRelativo;
$arquivoExiste   = !empty($arquivo) && file_exists($caminhoAbsoluto);

// Busca detalhes da proposta para exibir na tela (Nome do Cliente, Valor, etc)
$detalhes = null;
if ($id_proposta > 0) {
    $conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();
    $stmt = $conn->prepare("SELECT numero_proposta, nome_cliente_salvo, valor_final_proposta FROM Propostas WHERE id_proposta = ? AND id_criador = ?");
    $stmt->bind_param('ii', $id_proposta, $_SESSION['usuario_id']);
    $stmt->execute();
    $detalhes = $stmt->get_result()->fetch_assoc();
}

// Se não achou proposta ou não é dono, talvez redirecionar ou mostrar erro genérico
// Por enquanto, mostraremos o que temos.
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento Gerado | SGT</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body { 
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%); 
            color: white; 
            font-family: 'Inter', sans-serif; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            max-width: 900px;
            width: 95%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        /* Glow decorativo */
        .glow-spot {
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(37, 211, 102, 0.15) 0%, rgba(0,0,0,0) 70%);
            top: -100px;
            right: -100px;
            z-index: -1;
            pointer-events: none;
        }

        h1 { font-weight: 800; letter-spacing: -1px; margin-bottom: 0.5rem; }
        .text-success-bright { color: #4ade80; }
        
        .doc-preview {
            background: rgba(255,255,255,0.05);
            border: 1px dashed rgba(255,255,255,0.2);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            margin: 2rem 0;
            transition: all 0.3s ease;
        }
        .doc-preview:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.4);
        }

        .btn-download-word {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.5);
            width: 100%;
            justify-content: center;
            font-size: 1.1rem;
        }
        .btn-download-word:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.6);
            color: white;
        }

        .pdf-tip-box {
            background: rgba(245, 158, 11, 0.1); /* Amber tint */
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 2rem;
            font-size: 0.9rem;
            text-align: left;
        }

        .status-badge {
            background: rgba(74, 222, 128, 0.2);
            color: #4ade80;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .data-safe-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.05);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 2rem;
        }
    </style>
</head>
<body>

    <div class="dashboard-card text-center">
        <div class="glow-spot"></div>

        <!-- Cabeçalho -->
        <span class="status-badge"><i class="ph ph-check-circle"></i> Sucesso</span>
        <h1>Proposta Gerada!</h1>
        <p class="text-slate-400 mb-0">Seu documento foi criado e salvo com segurança.</p>
        
        <?php if ($detalhes): ?>
            <div class="mt-4 p-3 rounded bg-white/5 d-inline-block text-start border border-white/10" style="min-width: 300px;">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-secondary small">Proposta:</span>
                    <span class="fw-bold"><?php echo $detalhes['numero_proposta']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-secondary small">Cliente:</span>
                    <span class="fw-bold text-truncate" style="max-width: 200px;"><?php echo $detalhes['nome_cliente_salvo']; ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary small">Valor:</span>
                    <span class="fw-bold text-success-bright">R$ <?php echo number_format($detalhes['valor_final_proposta'], 2, ',', '.'); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Área Principal de Download -->
        <?php if ($arquivoExiste): ?>
            <div class="row mt-5 justify-content-center">
                <div class="col-md-8">
                    
                    <div class="doc-preview">
                        <i class="ph ph-file-doc text-white display-4 mb-3"></i>
                        <h3 class="h5 fw-bold mb-3">Arquivo Editável (.docx)</h3>
                        <p class="small text-secondary mb-4">Arquivo pronto para impressão ou edição final.</p>
                        
                        <a href="<?php echo $caminhoRelativo; ?>" class="btn-download-word" download>
                            <i class="ph ph-download-simple fw-bold"></i> BAIXAR WORD AGORA
                        </a>
                    </div>

                </div>
            </div>

            <!-- Dica PDF -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="pdf-tip-box text-start d-flex gap-3 align-items-start">
                        <i class="ph ph-file-pdf fs-3 text-warning flex-shrink-0"></i>
                        <div>
                            <strong class="text-warning d-block mb-1">Precisa do PDF?</strong>
                            <p class="mb-0 text-white-50 small">
                                O sistema gera o arquivo Word editável para máxima flexibilidade. 
                                Para enviar como PDF, abra o arquivo no Word e escolha <strong>"Salvar como PDF"</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados Seguros -->
            <div class="data-safe-badge">
                <i class="ph ph-shield-check text-success-bright"></i>
                Seus arquivos estão salvos no painel. Baixe quando quiser.
            </div>

            <!-- Botões de Navegação -->
            <div class="mt-5 d-flex justify-content-center gap-3">
                <a href="painel.php" class="btn btn-outline-light px-4 rounded-pill fw-bold" style="font-size: 0.9rem;">
                    <i class="ph ph-house me-2"></i> Voltar ao Painel
                </a>
                <a href="gerar_link_whatsapp.php?id=<?php echo $id_proposta; ?>" target="_blank" class="btn btn-outline-success px-4 rounded-pill fw-bold" style="font-size: 0.9rem; border-color: #25D366; color: #25D366;">
                    <i class="ph ph-whatsapp-logo me-2"></i> Enviar Link
                </a>
            </div>

        <?php else: ?>
            <div class="alert alert-danger mt-5">
                <h4 class="alert-heading">Arquivo não encontrado!</h4>
                <p>Houve um erro ao localizar o arquivo gerado (<?php echo htmlspecialchars($arquivo); ?>).</p>
                <hr>
                <a href="painel.php" class="btn btn-light btn-sm">Voltar ao Painel</a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>