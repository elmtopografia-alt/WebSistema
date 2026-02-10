<?php
session_start();
require_once 'config.php';

// Diretório onde os JSONs estão armazenados
$jsonDir = __DIR__;

// Função para listar todos os arquivos JSON relacionados a cenas
function listarArquivosJSON($dir) {
    $arquivos = [];
    $padroes = ['video_piloto_*.json', 'cod-*.json', '*_cena*.json'];
    
    foreach (glob($dir . '/*.json') as $arquivo) {
        $nomeArquivo = basename($arquivo);
        // Filtrar apenas JSONs relevantes (excluir composer.json etc)
        if (strpos($nomeArquivo, 'video_piloto') !== false || 
            strpos($nomeArquivo, 'cod-') !== false ||
            strpos($nomeArquivo, 'cena') !== false) {
            
            $arquivos[] = [
                'nome' => $nomeArquivo,
                'caminho' => $arquivo,
                'tamanho' => filesize($arquivo),
                'modificado' => filemtime($arquivo)
            ];
        }
    }
    
    // Ordenar por data de modificação (mais recente primeiro)
    usort($arquivos, function($a, $b) {
        return $b['modificado'] - $a['modificado'];
    });
    
    return $arquivos;
}

// Processar ações
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'salvar':
                $arquivo = $_POST['arquivo'];
                $conteudo = $_POST['conteudo'];
                
                // Validar JSON
                json_decode($conteudo);
                if (json_last_error() === JSON_ERROR_NONE) {
                    file_put_contents($jsonDir . '/' . $arquivo, $conteudo);
                    $mensagem = "Arquivo salvo com sucesso!";
                } else {
                    $erro = "Erro: JSON inválido - " . json_last_error_msg();
                }
                break;
                
            case 'criar':
                $nomeArquivo = $_POST['nome_arquivo'];
                if (!empty($nomeArquivo)) {
                    if (!str_ends_with($nomeArquivo, '.json')) {
                        $nomeArquivo .= '.json';
                    }
                    
                    $template = [
                        [
                            "description" => "",
                            "style" => "ultra-realistic, cinematic, professional, corporate realism",
                            "camera" => "smooth continuous camera movement",
                            "lighting" => "soft cinematic lighting",
                            "environment" => "modern professional auditorium",
                            "elements" => ["presenter", "Apple tablet", "digital board"],
                            "motion" => "",
                            "voice" => [
                                "text" => "",
                                "language" => "pt-BR",
                                "gender" => "male",
                                "tone" => "confident, professional"
                            ]
                        ]
                    ];
                    
                    file_put_contents($jsonDir . '/' . $nomeArquivo, json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $mensagem = "Arquivo criado com sucesso!";
                }
                break;
                
            case 'excluir':
                $arquivo = $_POST['arquivo'];
                if (file_exists($jsonDir . '/' . $arquivo)) {
                    unlink($jsonDir . '/' . $arquivo);
                    $mensagem = "Arquivo excluído com sucesso!";
                }
                break;
        }
    }
}

$arquivos = listarArquivosJSON($jsonDir);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Cenas JSON</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        
        .glass-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        
        .json-editor {
            font-family: 'Courier New', monospace;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #00ff88;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.5);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="min-h-screen p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="glass-card p-8 mb-8 slide-in">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">🎬 Gerenciador de Cenas JSON</h1>
                    <p class="text-gray-300">Gerencie todos os arquivos JSON de cenas de vídeo</p>
                </div>
                <button onclick="abrirModalCriar()" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                    ➕ Nova Cena
                </button>
            </div>
        </div>

        <?php if ($mensagem): ?>
            <div class="glass-card p-4 mb-6 border-green-500 bg-green-900/20 slide-in">
                <p class="text-green-300">✅ <?= htmlspecialchars($mensagem) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="glass-card p-4 mb-6 border-red-500 bg-red-900/20 slide-in">
                <p class="text-red-300">❌ <?= htmlspecialchars($erro) ?></p>
            </div>
        <?php endif; ?>

        <!-- Lista de Arquivos -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($arquivos as $arquivo): ?>
                <div class="glass-card p-6 cursor-pointer slide-in" onclick="abrirArquivo('<?= htmlspecialchars($arquivo['nome']) ?>')">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white mb-1">
                                📄 <?= htmlspecialchars($arquivo['nome']) ?>
                            </h3>
                            <p class="text-sm text-gray-400">
                                <?= number_format($arquivo['tamanho'] / 1024, 2) ?> KB
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between text-sm text-gray-400">
                        <span>📅 <?= date('d/m/Y H:i', $arquivo['modificado']) ?></span>
                        <div class="flex gap-2">
                            <button onclick="event.stopPropagation(); excluirArquivo('<?= htmlspecialchars($arquivo['nome']) ?>')" 
                                    class="text-red-400 hover:text-red-300">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($arquivos)): ?>
                <div class="col-span-3 glass-card p-12 text-center">
                    <p class="text-gray-400 text-lg mb-4">Nenhum arquivo JSON encontrado</p>
                    <button onclick="abrirModalCriar()" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                        Criar Primeira Cena
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div id="modalEditar" class="modal">
        <div class="glass-card p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-white">✏️ Editar Cena</h2>
                <button onclick="fecharModal('modalEditar')" class="text-gray-400 hover:text-white text-2xl">✕</button>
            </div>
            
            <form method="POST" onsubmit="return validarJSON()">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="arquivo" id="arquivoAtual">
                
                <div class="mb-4">
                    <label class="text-white font-semibold mb-2 block">Nome do Arquivo:</label>
                    <input type="text" id="nomeArquivoEditar" disabled 
                           class="w-full p-3 rounded-lg bg-gray-800/50 text-gray-400 border border-gray-700">
                </div>
                
                <div class="mb-6">
                    <label class="text-white font-semibold mb-2 block">Conteúdo JSON:</label>
                    <textarea name="conteudo" id="conteudoJSON" rows="20" 
                              class="w-full p-4 rounded-lg json-editor resize-none"
                              placeholder="Cole ou edite seu JSON aqui..."></textarea>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="btn-primary text-white px-8 py-3 rounded-lg font-semibold flex-1">
                        💾 Salvar Alterações
                    </button>
                    <button type="button" onclick="fecharModal('modalEditar')" 
                            class="bg-gray-700 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-600">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Criar -->
    <div id="modalCriar" class="modal">
        <div class="glass-card p-8 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-white">➕ Nova Cena</h2>
                <button onclick="fecharModal('modalCriar')" class="text-gray-400 hover:text-white text-2xl">✕</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="acao" value="criar">
                
                <div class="mb-6">
                    <label class="text-white font-semibold mb-2 block">Nome do Arquivo:</label>
                    <input type="text" name="nome_arquivo" 
                           class="w-full p-3 rounded-lg bg-gray-800/50 text-white border border-gray-700 focus:border-purple-500"
                           placeholder="Ex: cod-CN1 ou video_piloto_cena02"
                           required>
                    <p class="text-gray-400 text-sm mt-2">Extensão .json será adicionada automaticamente</p>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="btn-primary text-white px-8 py-3 rounded-lg font-semibold flex-1">
                        Criar Arquivo
                    </button>
                    <button type="button" onclick="fecharModal('modalCriar')" 
                            class="bg-gray-700 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-600">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirArquivo(nomeArquivo) {
            fetch('?ler=' + encodeURIComponent(nomeArquivo))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('arquivoAtual').value = nomeArquivo;
                    document.getElementById('nomeArquivoEditar').value = nomeArquivo;
                    document.getElementById('conteudoJSON').value = data;
                    document.getElementById('modalEditar').classList.add('active');
                });
        }

        function abrirModalCriar() {
            document.getElementById('modalCriar').classList.add('active');
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function excluirArquivo(nomeArquivo) {
            if (confirm('Tem certeza que deseja excluir ' + nomeArquivo + '?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="arquivo" value="${nomeArquivo}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function validarJSON() {
            const conteudo = document.getElementById('conteudoJSON').value;
            try {
                JSON.parse(conteudo);
                return true;
            } catch (e) {
                alert('❌ JSON inválido: ' + e.message);
                return false;
            }
        }

        // Fechar modal clicando fora
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>

<?php
// API para ler arquivo
if (isset($_GET['ler'])) {
    $arquivo = $_GET['ler'];
    $caminho = $jsonDir . '/' . $arquivo;
    
    if (file_exists($caminho)) {
        header('Content-Type: application/json');
        $conteudo = file_get_contents($caminho);
        
        // Formatar JSON para melhor legibilidade
        $json = json_decode($conteudo);
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
?>
