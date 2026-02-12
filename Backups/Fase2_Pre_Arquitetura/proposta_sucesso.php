<?php
// Nome do Arquivo: proposta_sucesso.php
// Função: Tela de sucesso. Configurada para REDIRECIONAR PARA FINAL.PHP (Vendas) após o clique.

session_start();
require_once 'config.php';

// Validação
if (!isset($_SESSION['usuario_id']) || !isset($_GET['arquivo'])) {
    header("Location: login.php");
    exit;
}

$arquivo = $_GET['arquivo'];
$id_proposta = $_GET['id'] ?? 0;
$caminhoDownload = 'propostas_emitidas/' . basename($arquivo);
$arquivoExiste = file_exists(__DIR__ . '/propostas_emitidas/' . basename($arquivo));

// --- CONFIGURAÇÃO PARA O VÍDEO ---
// Aqui forçamos a ida para a página de vendas, independente do usuário.
$url_destino = 'final.php'; 

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sucesso! | SGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .card-success { max-width: 500px; width: 95%; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); border: none; text-align: center; padding: 40px; }
        .icon-check { font-size: 5rem; color: #198754; margin-bottom: 20px; animation: pop 0.5s ease; }
        @keyframes pop { 0% { transform: scale(0); } 80% { transform: scale(1.1); } 100% { transform: scale(1); } }
        
        /* Botão de Download chamativo */
        .btn-download { 
            background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); 
            border: none; padding: 15px 30px; font-size: 1.2rem; border-radius: 50px; 
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); transition: transform 0.2s;
            text-decoration: none; color: white; display: inline-block; width: 100%; cursor: pointer;
        }
        .btn-download:hover { transform: scale(1.05); color: white; }
    </style>
</head>
<body>

    <div class="card card-success bg-white">
        <div class="icon-check">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        
        <h2 class="fw-bold text-dark mb-2">Proposta Gerada!</h2>
        <p class="text-muted mb-4">Seu arquivo está pronto para download.</p>
        
        <?php if($arquivoExiste): ?>
            
            <div class="d-grid gap-3">
                <!-- O CLICK AQUI DISPARA O DOWNLOAD E O REDIRECIONAMENTO -->
                <a href="<?php echo $caminhoDownload; ?>" class="btn-download fw-bold" download onclick="irParaVenda()">
                    <i class="bi bi-download me-2"></i> BAIXAR DOCX
                </a>

                <div class="row mt-3">
                    <div class="col-6">
                        <a href="gerar_link_whatsapp.php?id=<?php echo $id_proposta; ?>" target="_blank" class="btn btn-outline-success w-100">
                            <i class="bi bi-whatsapp"></i> Enviar
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="painel.php" class="btn btn-outline-secondary w-100">Voltar</a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-warning">O arquivo foi gerado, mas não encontrado.</div>
            <a href="painel.php" class="btn btn-secondary">Voltar</a>
        <?php endif; ?>
    </div>

    <script>
        function irParaVenda() {
            // Espera 1.5 segundos para o download começar e muda a tela
            setTimeout(function() {
                window.location.href = '<?php echo $url_destino; ?>';
            }, 1500);
        }
    </script>

</body>
</html>