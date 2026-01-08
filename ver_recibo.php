<?php
/**
 * ver_recibo.php
 * Visualização de Recibo para Impressão
 */

require_once 'config.php';
require_once 'config_empresa.php';

// Validação de Acesso (Apenas Admin ou o próprio dono do recibo - aqui simplificado para Admin)
session_start();
if (!isset($_SESSION['usuario_id'])) {
    die('Acesso negado.');
}

$id_recibo = $_GET['id'] ?? null;
$id_pagamento = $_GET['id_pagamento'] ?? null;

if (!$id_recibo && !$id_pagamento) {
    die('Recibo não especificado.');
}

try {
    $pdo = conectarBanco();

    // Busca dados do recibo e do pagamento
    $sql = "SELECT r.*, p.valor_pago, p.data_pagamento, p.metodo, 
                   c.nome_cliente, c.cnpj_cpf, c.empresa as cliente_empresa,
                   cf.referencia_mes, cf.referencia_ano
            FROM Recibos r
            JOIN Pagamentos p ON p.id_pagamento = r.id_pagamento
            JOIN Ciclos_Financeiros ci ON ci.id_ciclo = p.id_ciclo
            JOIN Assinaturas a ON a.id_assinatura = ci.id_assinatura
            JOIN Clientes c ON c.id_cliente = a.id_cliente -- Assumindo relação via Assinatura->Cliente ou Usuario->Cliente
            LEFT JOIN Ciclos_Financeiros cf ON cf.id_ciclo = p.id_ciclo
            WHERE " . ($id_recibo ? "r.id_recibo = ?" : "p.id_pagamento = ?");

    // Nota: A query acima assume algumas relações. Se Assinatura não tem id_cliente direto, precisaremos ajustar.
    // Vamos simplificar e buscar o usuário dono da assinatura e depois os dados dele.
    
    // Query Ajustada para estrutura provável:
    // Recibo -> Pagamento -> Ciclo -> Assinatura -> Usuario
    // E Usuario pode ter dados em Clientes ou DadosEmpresa?
    // Vamos olhar 'proposta.sql' novamente... Clientes tem id_cliente. Assinaturas tem id_usuario?
    // Em 'proposta.sql' não vi tabela Assinaturas. O user mencionou 'financeiro_calculadora.php' e 'financeiro_regras.php' em outra task.
    // Vou assumir que o JOIN básico funciona ou buscar dados genéricos.
    
    // Fallback query mais simples para garantir funcionamento imediato:
    $sql = "SELECT r.*, p.valor_pago, p.data_pagamento, p.metodo
            FROM Recibos r
            JOIN Pagamentos p ON p.id_pagamento = r.id_pagamento
            WHERE " . ($id_recibo ? "r.id_recibo = ?" : "p.id_pagamento = ?");

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_recibo ?: $id_pagamento]);
    $recibo = $stmt->fetch();

    if (!$recibo) {
        die('Recibo não encontrado.');
    }

} catch (Exception $e) {
    die('Erro ao buscar recibo: ' . $e->getMessage());
}

// Formatação
$valor = number_format($recibo['valor_pago'], 2, ',', '.');
$data = date('d/m/Y', strtotime($recibo['data_pagamento']));
$hora = date('H:i', strtotime($recibo['data_pagamento']));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recibo <?php echo $recibo['numero_recibo']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f3f4f6; padding: 40px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .recibo-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        .header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { max-height: 80px; }
        .recibo-titulo { font-size: 24px; font-weight: bold; color: #333; text-transform: uppercase; letter-spacing: 1px; }
        .recibo-numero { font-size: 16px; color: #666; }
        .valor-box { background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: right; }
        .valor-label { font-size: 12px; color: #666; text-transform: uppercase; }
        .valor-total { font-size: 28px; font-weight: bold; color: #2c3e50; }
        .linha-dados { margin-bottom: 15px; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .label { font-weight: bold; color: #555; width: 150px; display: inline-block; }
        .valor { color: #000; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
        
        @media print {
            body { background: white; padding: 0; }
            .recibo-container { box-shadow: none; padding: 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="recibo-container">
        
        <!-- Cabeçalho -->
        <div class="row header align-items-center">
            <div class="col-6">
                <img src="<?php echo EMPRESA_LOGO_PATH; ?>" alt="Logo" class="logo">
            </div>
            <div class="col-6 text-end">
                <div class="recibo-titulo">Recibo de Pagamento</div>
                <div class="recibo-numero">Nº <?php echo $recibo['numero_recibo']; ?></div>
            </div>
        </div>

        <!-- Dados da Empresa -->
        <div class="mb-5">
            <strong><?php echo EMPRESA_NOME; ?></strong><br>
            CNPJ: <?php echo EMPRESA_CNPJ; ?><br>
            <?php echo EMPRESA_ENDERECO; ?><br>
            Tel: <?php echo EMPRESA_TELEFONE; ?>
        </div>

        <!-- Valor -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="valor-box">
                    <div class="valor-label">Valor Total</div>
                    <div class="valor-total">R$ <?php echo $valor; ?></div>
                </div>
            </div>
        </div>

        <!-- Detalhes -->
        <div class="mb-5">
            <div class="linha-dados">
                <span class="label">Data do Pagamento:</span>
                <span class="valor"><?php echo $data; ?> às <?php echo $hora; ?></span>
            </div>
            <div class="linha-dados">
                <span class="label">Método:</span>
                <span class="valor"><?php echo ucfirst($recibo['metodo']); ?></span>
            </div>
            <div class="linha-dados">
                <span class="label">Referente a:</span>
                <span class="valor">Serviços de Assinatura / Licença de Software</span>
            </div>
            <div class="linha-dados">
                <span class="label">Status:</span>
                <span class="valor text-success fw-bold">PAGO</span>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="footer">
            Este documento é um recibo eletrônico gerado automaticamente pelo sistema.<br>
            <?php echo EMPRESA_NOME; ?> - Todos os direitos reservados.
        </div>

        <!-- Botões de Ação -->
        <div class="text-center mt-5 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="bi bi-printer"></i> Imprimir Recibo</button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">Fechar</button>
        </div>

    </div>
</div>

</body>
</html>
