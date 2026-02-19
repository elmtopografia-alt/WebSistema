require_once 'session_validator.php';
require_once 'config.php';
require_once 'PropostaRepository.php';

$id_usuario = $_SESSION['usuario_id'];
$id_proposta = intval($_GET['id']);

try {
    $repo = new PropostaRepository();
    $dados = $repo->buscarPorId($id_proposta);
    
    if (!$dados || $dados['id_criador'] != $id_usuario) {
        die("Proposta não encontrada ou acesso negado.");
    }
} catch (Exception $e) {
    die("Erro no sistema: " . $e->getMessage());
}

// 3. Define Caminho do Arquivo Anexo (LÓGICA REUTILIZADA DO PAINEL)
function buscarArquivoExistente($dados) {
    $ano = date('Y', strtotime($dados['data_criacao']));
    $numeroProposta = $dados['numero_proposta'];
    $parts = explode('-', $numeroProposta);
    $rawSeq = end($parts);
    $numPad = str_pad(preg_replace('/\D/', '', $rawSeq), 3, '0', STR_PAD_LEFT);
    $nomeEmpresa = trim(explode(' ', $dados['empresa_proponente_nome'])[0]);
    $nomeLimpo = preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nomeEmpresa));
    
    $arquivo = "{$nomeLimpo}-{$ano}-{$numPad}.docx";
    $dirBase = __DIR__ . '/propostas_emitidas';
    
    if (file_exists($dirBase . '/' . $arquivo)) return $arquivo;
    
    // Fallback por Glob
    $pattern = $dirBase . "/*-{$ano}-{$numPad}*.docx";
    $glob = glob($pattern);
    return ($glob) ? basename($glob[0]) : $arquivo;
}

$nome_arquivo = buscarArquivoExistente($dados);
$caminho_anexo = __DIR__ . '/propostas_emitidas/' . $nome_arquivo;
$arquivo_existe = file_exists($caminho_anexo);

// 5. Prepara Valores Padrão para o Formulário
$assunto_padrao = "Proposta " . $dados['numero_proposta'] . " - " . ($dados['nome_empresa'] ?? 'SGT');
$hora = date('H');
$saudacao = ($hora < 12) ? 'Bom dia' : (($hora < 18) ? 'Boa tarde' : 'Boa noite');
$primeiro_nome = explode(' ', trim($dados['nome_cliente_salvo'] ?? 'Cliente'))[0];

// Gera Link para Download da Proposta
$link_proposta = "";
if ($arquivo_existe) {
    $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $base_url = $protocolo . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
    $link_proposta = $base_url . "/propostas_emitidas/" . rawurlencode($nome_arquivo);
}

$mensagem_padrao  = "$saudacao, $primeiro_nome.\n\n";
$mensagem_padrao .= "Conforme solicitado, segue o link para acessar a proposta para o serviço de " . ($dados['nome_servico'] ?? 'Topografia') . ".\n\n";
$mensagem_padrao .= "📄 **Acesse a Proposta aqui:**\n$link_proposta\n\n";
$mensagem_padrao .= "Estou à disposição para sanar dúvidas e negociarmos as condições.\n\n";
$mensagem_padrao .= "Atenciosamente,\n";
$mensagem_padrao .= $dados['nome_empresa'] ?? 'SGT Topografia';

$mailto_link = "mailto:" . ($dados['email_cliente'] ?? $dados['email_salvo'] ?? '') . 
               "?subject=" . rawurlencode($assunto_padrao) . 
               "&body=" . rawurlencode($mensagem_padrao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Proposta | SGT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Exo 2', 'sans-serif'] },
                    colors: { background: '#0a0f1a', surface: '#111827', primary: '#f97316' }
                }
            }
        }
    </script>
    <style>
        body { background: #0a0f1a; color: #f1f5f9; }
        .glass { background: rgba(17,24,39,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
        .grid-bg { background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 40px 40px; }
    </style>
</head>
<body class="antialiased min-h-screen grid-bg">

    <?php include 'components/navbar.php'; ?>

    <main class="max-w-4xl mx-auto px-4 py-8 relative z-10">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20">
                    <i class="ph ph-paper-plane-tilt text-2xl"></i>
                </div>
                <div>
                    <h1 class="font-display text-2xl font-bold">Enviar Proposta</h1>
                    <p class="text-sm text-slate-400"><?= $dados['numero_proposta'] ?> • <?= htmlspecialchars($dados['nome_cliente_salvo']) ?></p>
                </div>
            </div>
            <a href="painel.php" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2 text-sm font-medium">
                <i class="ph ph-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Side -->
            <div class="lg:col-span-2 space-y-6">
                <div class="glass p-6 rounded-2xl">
                    <form class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Destinatário</label>
                            <input type="email" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary transition-all" value="<?= htmlspecialchars($dados['email_cliente'] ?? $dados['email_salvo'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Assunto</label>
                            <input type="text" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary transition-all" value="<?= htmlspecialchars($assunto_padrao) ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Mensagem</label>
                            <textarea rows="10" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary transition-all resize-none font-mono text-sm"><?= htmlspecialchars($mensagem_padrao) ?></textarea>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Action Side -->
            <div class="space-y-6">
                <!-- Status do Arquivo -->
                <div class="glass p-6 rounded-2xl <?= $arquivo_existe ? 'border-green-500/20 bg-green-500/5' : 'border-yellow-500/20 bg-yellow-500/5' ?>">
                    <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                        <i class="ph ph-paperclip <?= $arquivo_existe ? 'text-green-400' : 'text-yellow-400' ?>"></i> 
                        Anexo DOCX
                    </h3>
                    <?php if ($arquivo_existe): ?>
                        <p class="text-xs text-slate-300 break-all mb-4"><?= $nome_arquivo ?></p>
                        <a href="propostas_emitidas/<?= $nome_arquivo ?>" download class="flex items-center justify-center gap-2 w-full py-2 bg-white/10 hover:bg-white/20 rounded-lg text-xs font-bold transition-all">
                            <i class="ph ph-download"></i> Baixar Arquivo
                        </a>
                    <?php else: ?>
                        <p class="text-xs text-yellow-400/80 mb-2 font-medium">Arquivo não encontrado no servidor.</p>
                        <p class="text-[10px] text-slate-500 leading-relaxed">Você pode enviar o texto e o link, mas o arquivo físico precisará ser regenerado ou anexado manualmente.</p>
                    <?php endif; ?>
                </div>

                <!-- Botões de Envio -->
                <div class="space-y-3">
                    <div class="p-4 bg-orange-500/10 border border-orange-500/20 rounded-xl mb-4">
                        <p class="text-[10px] text-orange-400 font-bold uppercase tracking-widest mb-1">Nota Técnica</p>
                        <p class="text-[11px] text-slate-400 leading-relaxed">O envio direto via servidor está em manutenção. Utilize o link abaixo para abrir seu app de email.</p>
                    </div>
                    
                    <a href="<?= $mailto_link ?>" class="flex items-center justify-center gap-3 w-full py-4 bg-primary hover:bg-orange-600 text-white font-bold rounded-2xl shadow-lg shadow-orange-900/20 transition-all hover:scale-[1.02] active:scale-95">
                        <i class="ph ph-envelope-open text-xl"></i> Abrir no Outlook/Gmail
                    </a>
                    
                    <button onclick="copyLink()" class="flex items-center justify-center gap-3 w-full py-3 bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 font-medium rounded-xl transition-all text-sm" id="btnCopy">
                        <i class="ph ph-copy text-lg"></i> Copiar Link Manual
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        function copyLink() {
            const link = '<?= $link_proposta ?>';
            navigator.clipboard.writeText(link);
            const btn = document.getElementById('btnCopy');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-check text-green-400 text-lg"></i> Link Copiado!';
            setTimeout(() => btn.innerHTML = original, 2000);
        }
    </script>
</body>
</html>