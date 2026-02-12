<?php
// preview_proposta.php (MOCK)
// Simula a visualização da proposta em HTML (estilo PDF)

// Recebe os dados do POST
$dados = $_POST;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Preview da Proposta</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.5;
            color: #000;
            background: #525659;
            margin: 0;
            padding: 20px;
        }

        .page {
            background: #fff;
            width: 21cm;
            min-height: 29.7cm;
            padding: 2.5cm 3cm;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        h1 {
            font-size: 24pt;
            color: #1e3a8a;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 14pt;
            color: #333;
            margin-top: 20px;
            border-bottom: 1px solid #ccc;
        }

        p {
            text-align: justify;
            margin-bottom: 15px;
        }

        .header-info {
            text-align: right;
            font-size: 10pt;
            color: #666;
            margin-bottom: 50px;
        }

        .tag {
            color: red;
            font-weight: bold;
        }

        /* Destaca variáveis não preenchidas */
    </style>
</head>

<body>

    <div class="page">
        <!-- Capa Simulada -->
        <div class="header-info">
            Proposta Comercial<br>
            Data: <?= date('d/m/Y') ?>
        </div>

        <h1><?= $dados['project_name'] ?? 'Projeto Sem Nome' ?></h1>
        <p><strong>Cliente:</strong> <?= $dados['client_name'] ?? 'Cliente Não Definido' ?></p>

        <!-- Simulação de Conteúdo Dinâmico -->
        <?php
        // Se tiver Escopo Técnico (Variável vinda do form)
        if (!empty($dados['technical_scope_content'])) {
            echo nl2br($dados['technical_scope_content']);
        } else {
            echo "<p>Aqui entraria o texto padrão do <strong>Escopo Técnico</strong> (o arquivo longo que inserimos no banco).</p>";
            echo "<p>Como esta é uma pré-visualização rápida, imagine aqui os itens:</p>";
            echo "<ul><li>Levantamento Planimétrico</li><li>Processamento</li><li>Desenho</li></ul>";
        }
        ?>

        <!-- Investimento Simulada -->
        <h2>Investimento</h2>
        <p>Valor Total: <strong><?= $dados['total_value'] ?? 'R$ 0,00' ?></strong></p>
        <p>Condições: <?= $dados['conditions'] ?? 'À combinar' ?></p>

        <hr style="margin-top: 50px;">
        <p style="text-align: center; font-size: 0.8em; color: #999;">Pré-visualização de Layout (Fatura de Teste)</p>
    </div>

</body>

</html>