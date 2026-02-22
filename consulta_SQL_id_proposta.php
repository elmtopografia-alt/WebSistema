<?php
/**
 * consulta_SQL_id_proposta.php
 * Aplicativo para consultar registros da tabela Propostas pelo id_proposta
 * Layout 16:9 widescreen
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$conn = Database::getProd();

$id_proposta = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT) ?? '';
$erro = '';
$proposta = null;

if (!empty($id_proposta)) {
    try {
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Container 16:9 - FORÇADO via aspect-ratio */
        .widescreen-container {
            width: 95vw;
            max-width: 1400px;
            aspect-ratio: 16/9;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Header compacto */
        .app-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
        }

        .app-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .app-header i {
            font-size: 2rem;
        }

        /* Área de conteúdo - ocupa espaço restante */
        .app-body {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Formulário de busca compacto */
        .search-section {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 20px;
            flex-shrink: 0;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-size: 0.85rem;
            color: #555;
            font-weight: 500;
        }

        .form-control {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            width: 200px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-primary {
            padding: 10px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            height: 42px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Alerta de erro */
        .alert-danger {
            background: #fee;
            color: #c33;
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 4px solid #c33;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        /* Container da tabela - ocupa todo espaço restante */
        .table-wrapper {
            flex: 1;
            overflow: auto;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            background: #fafafa;
        }

        .table-wrapper::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        /* Tabela flexível */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            /* REMOVIDO: table-layout: fixed; */
        }

        .data-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table th {
            background: #f0f0f0;
            color: #333;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }

        /* Ocupa apenas o tamanho do texto do campo, sem cortar */
        .data-table th:first-child,
        .data-table td:first-child {
            width: 1%;
            white-space: nowrap;
            padding-right: 25px;
        }

        /* COLUNA VALOR OCUPA RESTO */
        .data-table th:last-child,
        .data-table td:last-child {
            width: auto;
        }

        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        /* Estilo da coluna campo */
        .field-name {
            font-weight: 600;
            color: #444;
            /* REMOVIDO: text-overflow/overflow limitadores */
        }

        /* Estilo da coluna valor - texto longo */
        .field-value {
            color: #333;
            line-height: 1.6;
            word-wrap: break-word;
            word-break: break-word;
        }

        /* Texto longo com formatação preservada */
        .field-value pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
            font-family: inherit;
            background: rgba(0,0,0,0.03);
            padding: 10px;
            border-radius: 6px;
            max-height: 200px;
            overflow-y: auto;
        }

        .data-table tbody tr:hover {
            background: #f5f5f5;
        }

        /* Footer */
        .app-footer {
            padding: 15px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            flex-shrink: 0;
        }

        .app-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .app-footer a:hover {
            text-decoration: underline;
        }

        /* Responsivo: em telas pequenas, desativa aspect-ratio */
        @media (max-width: 900px) {
            .widescreen-container {
                aspect-ratio: auto;
                min-height: 90vh;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="widescreen-container">
    <header class="app-header">
        <i class="bi bi-search"></i>
        <div>
            <h1>Consulta de Proposta</h1>
            <small>Sistema de Gerenciamento de Propostas</small>
        </div>
    </header>

    <main class="app-body">
        <?php if ($erro): ?>
            <div class="alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($erro) ?></span>
            </div>
        <?php endif; ?>

        <form method="get" action="consulta_SQL_id_proposta.php" class="search-section">
            <div class="form-group">
                <label for="id">ID da Proposta</label>
                <input type="number" name="id" id="id" class="form-control" 
                       required placeholder="Ex: 123" 
                       value="<?= htmlspecialchars($id_proposta) ?>">
            </div>
            <button type="submit" class="btn-primary">
                <i class="bi bi-search"></i> Buscar
            </button>
        </form>

        <?php if ($proposta): ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>CAMPO</th>
                            <th>VALOR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proposta as $coluna => $valor): ?>
                            <tr>
                                <td class="field-name" title="<?= htmlspecialchars($coluna) ?>">
                                    <?= htmlspecialchars($coluna) ?>
                                </td>
                                <td class="field-value">
                                    <?php if (strlen($valor) > 80 || strpos($valor, "\n") !== false): ?>
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
        <?php else: ?>
            <div style="flex:1; display:flex; align-items:center; justify-content:center; color:#999;">
                <div style="text-align:center;">
                    <i class="bi bi-inbox" style="font-size:3rem; display:block; margin-bottom:10px;"></i>
                    Nenhuma proposta carregada. Digite um ID acima.
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="app-footer">
        <a href="../painel.php"><i class="bi bi-arrow-left"></i> Voltar ao painel</a>
    </footer>
</div>

</body>
</html>