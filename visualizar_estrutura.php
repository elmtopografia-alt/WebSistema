<?php
require_once 'vendor/autoload.php';

use ProposalArchitect\Models\CorporativoPremiumModel;
use ProposalArchitect\Infrastructure\HierarchyTreeBuilder;
use ProposalArchitect\Infrastructure\StructuralComplexityAnalyzer;
use ProposalArchitect\Infrastructure\VariableAggregator;

// Instanciar o motor
$model = new CorporativoPremiumModel();
$treeBuilder = new HierarchyTreeBuilder();
$analyzer = new StructuralComplexityAnalyzer();
$aggregator = new VariableAggregator();

// Processar dados
$tree = $treeBuilder->build($model);
$metrics = $analyzer->analyze($model);
$variables = $aggregator->extractRequiredVariables($model);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProposalArchitect™ | Visualizador de Estrutura</title>
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --primary: #2563eb;
            --tech: #0ea5e9;
            --financial: #10b981;
            --legal: #64748b;
            --presentation: #8b5cf6;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .metric-card {
            background: var(--card);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            margin-bottom: 1rem;
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
        }

        .metric-label {
            color: #64748b;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .tree-container {
            background: var(--card);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        .block-item {
            border-left: 4px solid #ddd;
            padding: 1rem;
            margin: 0.5rem 0;
            background: #f1f5f9;
            border-radius: 0 8px 8px 0;
            position: relative;
        }

        .cat-technical {
            border-color: var(--tech);
            background: #e0f2fe;
        }

        .cat-financial {
            border-color: var(--financial);
            background: #d1fae5;
        }

        .cat-legal {
            border-color: var(--legal);
            background: #f1f5f9;
        }

        .cat-presentation {
            border-color: var(--presentation);
            background: #ede9fe;
        }

        .level-0 {
            margin-left: 0;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .level-1 {
            margin-left: 2rem;
        }

        .level-2 {
            margin-left: 4rem;
            font-size: 0.9rem;
        }

        .badges {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.8);
            font-weight: 600;
        }

        .badge-req {
            color: #ef4444;
            border: 1px solid #ef4444;
        }

        h1 {
            grid-column: 1 / -1;
            margin-bottom: 2rem;
            color: var(--primary);
        }
    </style>
</head>

<body>
    <h1>🏗️ ProposalArchitect™ <small style="font-size: 0.5em; color: #666; font-weight: normal;">Visualização de Estrutura</small></h1>

    <div class="container">
        <!-- Sidebar: Métricas -->
        <aside>
            <div class="metric-card">
                <div class="metric-label">Complexity Score</div>
                <div class="metric-value"><?= $metrics['complexity_score'] ?></div>
                <p style="font-size: 0.8rem; color: #666; margin-top: 0.5rem;">
                    Nível de profundidade e detalhe da proposta. Ideal entre 100-300.
                </p>
            </div>

            <div class="metric-card">
                <div class="metric-label">Total de Blocos</div>
                <div class="metric-value"><?= $metrics['total_blocks'] + $metrics['total_sub_blocks'] ?></div>
            </div>

            <div class="metric-card">
                <div class="metric-label">Variáveis Requeridas</div>
                <ul style="font-size: 0.85rem; padding-left: 1rem; margin-top: 0.5rem; color: #475569;">
                    <?php
                    $uniqueVars = array_unique(array_column($variables, 'variable'));
                    foreach (array_slice($uniqueVars, 0, 10) as $var): ?>
                        <li><?= $var ?></li>
                    <?php endforeach; ?>
                    <?php if (count($uniqueVars) > 10): ?>
                        <li>...e mais <?= count($uniqueVars) - 10 ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>

        <!-- Main: Árvore Visual -->
        <main class="tree-container">
            <h2 style="margin-top:0;">Estrutura do Modelo: <?= $model->getModelMetadata()['name'] ?></h2>

            <?php
            function renderTree($nodes)
            {
                foreach ($nodes as $node) {
                    $catClass = 'cat-' . $node['category'];
                    $levelClass = 'level-' . min($node['level'], 2);
                    $reqBadge = $node['required'] ? '<span class="badge badge-req">OBRIGATÓRIO</span>' : '';

                    echo "<div class='block-item {$catClass} {$levelClass}'>";
                    echo "<div>{$node['title']} <span style='font-size:0.8em; opacity:0.6; margin-left:0.5rem;'>#{$node['id']}</span></div>";
                    echo "<div class='badges'>{$reqBadge} <span class='badge'>{$node['category']}</span></div>";
                    echo "</div>";

                    if (!empty($node['children'])) {
                        renderTree($node['children']);
                    }
                }
            }
            renderTree($tree);
            ?>
        </main>
    </div>
</body>

</html>