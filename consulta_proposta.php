<?php
/**
 * consulta_proposta.php
 * Aplicativo para consultar registros da tabela Propostas pelo id_proposta
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Proteção básica (verificando se há um admin/usuário logado, se necessário)
// O prompt não diz que tem que estar logado, mas como lida com id_proposta, é bom pelo menos tentar carregar a DB.
$conn = Database::getProd();

$id_proposta = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT) ?? '';
$erro = '';
$proposta = null;

if (!empty($id_proposta)) {
    try {
        // Busca todos os dados da proposta
        $stmt = $conn->prepare("SELECT * FROM Propostas WHERE id_proposta = ? LIMIT 1");
        $stmt->bind_param('i', $id_proposta);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $proposta = $result->fetch_assoc();
        } else {
            $erro = 'Nenhum registro encontrado para o ID fornecido.';
        }
    } catch (Exception $e) {
        $erro = 'Erro ao consultar o banco de dados: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Proposta | <?php echo defined('SITE_NAME') ? SITE_NAME : 'SGT'; ?></title>
    <!-- CSS Premium (Glassmorphism / SGT Dark Theme) -->
    <link rel="stylesheet" href="assets/css/auth-premium.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Estilos adicionais para os dados, respeitando o Glassmorphism */
        .data-container {
            margin-top: 20px;
            text-align: left;
            width: 100%;
            max-height: 600px; /* Aumentado para proporção 16:9 */
            overflow-y: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
        }

        .data-container::-webkit-scrollbar {
            width: 8px;
        }
        .data-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 4px;
        }
        .data-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .data-table {
            width: 100%;
            table-layout: fixed; /* Importante: força larguras fixas */
            border-collapse: collapse;
            font-size: 0.9rem;
            color: #333;
        }

        .data-table th, .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eaeaea;
            text-align: left;
            vertical-align: top;
        }

        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #2c3e50;
            font-size: 0.8rem;
        }

        .data-table tbody tr {
            transition: background 0.2s ease;
        }

        .data-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        /* Coluna das chaves (Campo) - MAIS ESTREITA */
        .data-key {
            font-weight: 500;
            color: #2c3e50;
            white-space: nowrap;
            width: 150px; /* Largura fixa pequena para o nome do campo */
            padding-right: 20px !important;
        }

        /* Coluna dos valores (Valor) - MAIS LARGA */
        .data-value {
            word-wrap: break-word;
            word-break: break-word; /* Melhor que break-all para textos */
            color: #333;
            width: calc(100% - 150px); /* Ocupa todo o restante do espaço */
            line-height: 1.5; /* Melhor legibilidade para textos longos */
        }
        
        /* Card mais largo (16:9) */
        .auth-card {
            width: 95%; /* Usar quase toda a largura disponível */
            max-width: 1200px; /* Aumentado significativamente */
            min-height: 500px; /* Altura mínima para proporção mais horizontal */
        }

        /* Ajuste para textos muito longos - preserva formatação */
        .data-value pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
            font-family: inherit;
            background: rgba(0,0,0,0.03);
            padding: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="ambient-glow"></div>

<main class="auth-container">
    <section class="auth-card">
        <header class="auth-header">
            <h1>
                <div class="icon-logo"><i class="bi bi-search"></i></div>
                Consulta Proposta
            </h1>
            <p>Digite o ID para ver os registros</p>
        </header>

        <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert" style="background: rgba(220, 53, 69, 0.2); color: #ff6b6b; padding: 12px; border-radius: 8px; border: 1px solid rgba(220, 53, 69, 0.3); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= htmlspecialchars($erro) ?></div>
            </div>
        <?php endif; ?>

        <form method="get" action="consulta_proposta.php">
            <div class="form-group">
                <label for="id" class="form-label">ID da Proposta (id_proposta)</label>
                <div class="input-group" style="display: flex; gap: 10px; align-items: center;">
                    <input type="number" name="id" id="id" class="form-control" required placeholder="Ex: 123" value="<?= htmlspecialchars($id_proposta) ?>" style="flex: 1;">
                    <button type="submit" class="btn-primary" style="width: auto; padding: 0 20px;">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
        
        <?php if ($proposta): ?>
            <div class="data-container">
                <h3 style="color: #2c3e50; font-size: 1.1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-file-earmark-text"></i> Detalhes da Proposta #<?= htmlspecialchars($proposta['id_proposta']) ?>
                </h3>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Campo</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proposta as $coluna => $valor): ?>
                            <tr>
                                <td class="data-key"><?= htmlspecialchars($coluna) ?></td>
                                <td class="data-value">
                                    <?php 
                                    // Detecta se o valor parece ter múltiplas linhas ou é muito longo
                                    if (strlen($valor) > 100 || strpos($valor, "\n") !== false): 
                                    ?>
                                        <pre><?= htmlspecialchars($valor) ?></pre>
                                    <?php else: ?>
                                        <?= htmlspecialchars((string)$valor) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <footer class="auth-footer" style="margin-top: 25px;">
            <a href="painel.php" class="link-secondary">Voltar ao painel</a>
        </footer>
    </section>
</main>

</body>
</html>