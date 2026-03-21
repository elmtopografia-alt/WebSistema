<?php
/**
 * EDITAR PROPOSTA - SGT Propostas v2
 * Edição com suporte a modelo único + cor
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ConnectionManager.php';
require_once __DIR__ . '/core/TemaEngine.php';
require_once __DIR__ . '/core/ModeloBase.php';
require_once __DIR__ . '/ResolvedorChavesSistema.php';

use SGT\Core\TemaEngine;

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'SGT\\Modelos\\';
    $baseDir = __DIR__ . '/modelos/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) require $file;
});

// Configurações
$temas = [
    'azul' => ['nome' => 'Corporativo', 'icone' => '🏢', 'hex' => '#1e3a8a'],
    'verde' => ['nome' => 'Topografia', 'icone' => '🌿', 'hex' => '#065f46'],
    'laranja' => ['nome' => 'Energia', 'icone' => '⚡', 'hex' => '#7c2d12'],
    'cinza' => ['nome' => 'Institucional', 'icone' => '📋', 'hex' => '#1f2937'],
];

// Buscar proposta
$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    die("ID da proposta não informado");
}

try {
    $conn = ConnectionManager::get();
    $stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $proposta = $res->fetch_assoc();
    
    if (!$proposta) {
        die("Proposta não encontrada");
    }
    
} catch (Exception $e) {
    die("Erro ao buscar proposta: " . $e->getMessage());
}

// Processar atualização
$mensagem = '';
$previewHtml = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    $modeloNome = $_POST['modelo'] ?? $proposta['modelo_docx'];
    $cor = $_POST['cor'] ?? $proposta['cor'] ?? 'verde';
    $dadosManual = $_POST['dados'] ?? json_decode($proposta['dados_manual'] ?? '{}', true);
    
    if ($acao === 'atualizar' || $acao === 'preview') {
        // Atualizar no banco
        try {
            $stmt = $conn->prepare("
                UPDATE Propostas 
                SET modelo_docx = ?, cor = ?, dados_manual = ?, data_atualizacao = NOW()
                WHERE id_proposta = ?
            ");
            $dadosJson = json_encode($dadosManual);
            $stmt->bind_param("sssi", $modeloNome, $cor, $dadosJson, $id);
            $stmt->execute();
            
            // Recarregar proposta atualizada
            $stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $proposta = $res->fetch_assoc();
            
            if ($acao === 'atualizar') {
                $mensagem = "✅ Proposta #{$id} atualizada com sucesso!";
            }
            
        } catch (Exception $e) {
            $mensagem = "❌ Erro ao atualizar: " . $e->getMessage();
        }
    }
    
    // Gerar preview
    if ($modeloNome) {
        $classe = "SGT\\Modelos\\{$modeloNome}";
        $resolvedor = new ResolvedorChavesSistema();
        
        try {
            $modelo = new $classe($cor);
            $previewHtml = $modelo->render($dadosManual, $resolvedor, $_SESSION['usuario_id'] ?? 1);
        } catch (Exception $e) {
            $mensagem = "Erro ao gerar preview: " . $e->getMessage();
        }
    }
}

// Dados atuais
$modeloAtual = $proposta['modelo_docx'] ?? 'PropostaDrone';
$corAtual = $proposta['cor'] ?? 'verde';
$dadosAtuais = json_decode($proposta['dados_manual'] ?? '{}', true);

// Buscar clientes
$clientes = [];
try {
    $res = $conn->query("SELECT id_cliente, nome_cliente, email FROM Clientes ORDER BY nome_cliente");
    if ($res) {
        while($r = $res->fetch_assoc()) {
            $clientes[] = $r;
        }
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Proposta #<?= $id ?> - SGT Propostas v2</title>
    <style>
        /* Mesmos estilos do criar_proposta_dinamica.php */
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        h1 { margin: 0; font-size: 1.5rem; }
        .proposta-id { opacity: 0.9; font-size: 0.9rem; margin-top: 5px; }
        
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
        @media (max-width: 1024px) { .grid { grid-template-columns: 1fr; } }
        
        .painel { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h2 { font-size: 1.25rem; color: #1f2937; margin-top: 0; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; color: #374151; }
        select, input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; }
        
        .cor-opcao { border: 2px solid #e5e7eb; border-radius: 8px; padding: 12px; text-align: center; cursor: pointer; }
        .cor-opcao.selecionada { box-shadow: 0 0 0 3px currentColor; }
        .cor-opcao input { display: none; }
        .cor-preview { width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 8px; }
        
        .btn { padding: 12px 24px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-secondary { background: #f3f4f6; color: #374151; }
        .btn-success { background: #10b981; color: white; }
        
        .acoes { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        
        .preview-container { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .preview-header { background: #f9fafb; padding: 15px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; }
        .preview-content { padding: 20px; max-height: 70vh; overflow-y: auto; }
        
        .mensagem { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .mensagem.sucesso { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .mensagem.erro { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
        
        .info-box { background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 15px; margin-bottom: 20px; }
        .info-box label { margin-bottom: 0; }
        
        .dados-rapidos { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .secao-titulo { font-size: 14px; font-weight: 700; color: #6b7280; text-transform: uppercase; margin: 20px 0 10px; padding-bottom: 5px; border-bottom: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>✏️ Editar Proposta</h1>
            <div class="proposta-id">ID: #<?= $id ?> | Criada em: <?= date('d/m/Y H:i', strtotime($proposta['data_criacao'])) ?></div>
        </header>
        
        <?php if ($mensagem): ?>
            <div class="mensagem <?= strpos($mensagem, '✅') !== false ? 'sucesso' : 'erro' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" id="formEditar">
            <input type="hidden" name="id" value="<?= $id ?>">
            
            <div class="grid">
                <!-- Coluna Esquerda -->
                <div>
                    <!-- Status atual -->
                    <div class="info-box">
                        <label>Modelo atual: <strong><?= htmlspecialchars($modeloAtual) ?></strong></label><br>
                        <label>Tema atual: <strong><?= $temas[$corAtual]['nome'] ?? $corAtual ?></strong></label>
                    </div>
                    
                    <!-- Alterar Tema -->
                    <div class="painel">
                        <h2>Alterar Tema Visual</h2>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                            <?php foreach ($temas as $key => $tema): ?>
                                <label class="cor-opcao <?= $corAtual === $key ? 'selecionada' : '' ?>" 
                                       style="color: <?= $tema['hex'] ?>">
                                    <input type="radio" name="cor" value="<?= $key ?>" 
                                           <?= $corAtual === $key ? 'checked' : '' ?>>
                                    <div class="cor-preview" style="background: <?= $tema['hex'] ?>"></div>
                                    <div style="font-size: 12px;"><?= $tema['icone'] ?> <?= $tema['nome'] ?></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Editar Dados -->
                    <div class="painel">
                        <h2>Dados da Proposta</h2>
                        
                        <div class="form-group">
                            <label>Cliente</label>
                            <select name="cliente_id" disabled>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?= $c['id_cliente'] ?>" <?= $proposta['id_cliente'] == $c['id_cliente'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nome_cliente']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color: #6b7280;">Cliente não pode ser alterado. Crie nova proposta se necessário.</small>
                        </div>
                        
                        <div class="secao-titulo">Dados da Obra</div>
                        <div class="dados-rapidos">
                            <div class="form-group">
                                <label>Área (m²)</label>
                                <input type="number" name="dados[AreaEstimada]" 
                                       value="<?= htmlspecialchars($dadosAtuais['AreaEstimada'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Tipo Terreno</label>
                                <input type="text" name="dados[TipoTerreno]" 
                                       value="<?= htmlspecialchars($dadosAtuais['TipoTerreno'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Cobertura</label>
                                <input type="text" name="dados[CoberturaVegetal]" 
                                       value="<?= htmlspecialchars($dadosAtuais['CoberturaVegetal'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Acesso</label>
                                <input type="text" name="dados[AcessoLocal]" 
                                       value="<?= htmlspecialchars($dadosAtuais['AcessoLocal'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="secao-titulo">Valores</div>
                        <div class="dados-rapidos">
                            <div class="form-group">
                                <label>Valor Total (R$)</label>
                                <input type="text" name="dados[ValorProposta]" 
                                       value="<?= htmlspecialchars($dadosAtuais['ValorProposta'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Mobilização (%)</label>
                                <input type="number" name="dados[mobilizacao_percentual]" 
                                       value="<?= htmlspecialchars($dadosAtuais['mobilizacao_percentual'] ?? '50') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ações -->
                    <div class="painel">
                        <div class="acoes">
                            <button type="submit" name="acao" value="preview" class="btn btn-secondary">
                                👁️ Preview
                            </button>
                            <button type="submit" name="acao" value="atualizar" class="btn btn-primary">
                                💾 Salvar Alterações
                            </button>
                        </div>
                        <div class="acoes" style="margin-top: 10px;">
                            <a href="visualizar_proposta.php?id=<?= $id ?>" class="btn btn-success" style="text-decoration: none; display: inline-block;">
                                📄 Visualizar Final
                            </a>
                            <a href="lista_propostas.php" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">
                                ← Voltar
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Coluna Direita: Preview -->
                <div>
                    <div class="preview-container">
                        <div class="preview-header">
                            <strong>Preview Atualizado</strong>
                            <span style="color: #6b7280; font-size: 12px;">
                                Tema: <?= $temas[$corAtual]['nome'] ?? $corAtual ?>
                            </span>
                        </div>
                        <div class="preview-content">
                            <?php if ($previewHtml): ?>
                                <?= $previewHtml ?>
                            <?php else: ?>
                                <?php
                                // Preview inicial com dados salvos
                                try {
                                    $classe = "SGT\\Modelos\\{$modeloAtual}";
                                    $resolvedor = new ResolvedorChavesSistema();
                                    $modelo = new $classe($corAtual);
                                    echo $modelo->render($dadosAtuais, $resolvedor, $_SESSION['usuario_id'] ?? 1);
                                } catch (Exception $e) {
                                    echo "<p style='color: #ef4444;'>Erro ao carregar preview: " . $e->getMessage() . "</p>";
                                }
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        document.querySelectorAll('.cor-opcao input').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.cor-opcao').forEach(el => el.classList.remove('selecionada'));
                this.closest('.cor-opcao').classList.add('selecionada');
            });
        });
    </script>
</body>
</html>