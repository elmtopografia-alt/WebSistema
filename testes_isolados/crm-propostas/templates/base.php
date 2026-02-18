<?php
/**
 * TEMPLATE BASE - Todas as propostas usam este arquivo
 * 
 * Variáveis esperadas:
 * - $tema_info: array com info do tema (vindo de carregarTema())
 * - $proposta: array com dados da proposta
 * - $config: array com config da empresa
 */

if (!defined('SGT_PROPOSTAS')) {
    die('Acesso direto não permitido');
}

// Extrai variáveis para facilitar
$empresa = $config['empresa'];
$banco = $config['banco'];
$tema = $tema_info;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta <?= htmlspecialchars($proposta['numero']) ?> - <?= htmlspecialchars($empresa['nome']) ?></title>
    
    <!-- Fontes do tema -->
    <?php foreach ($tema['fontes'] as $fonte): ?>
        <?php if ($fonte === 'Inter'): ?>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <?php elseif ($fonte === 'Playfair Display'): ?>
            <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
        <?php elseif ($fonte === 'JetBrains Mono'): ?>
            <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
        <?php endif; ?>
    <?php endforeach; ?>
    
    <style>
        /* CSS Base (inline para funcionar em qualquer servidor) */
        <?= file_get_contents(__DIR__ . '/../assets/css/base.css') ?>
        
        /* CSS do Tema Selecionado */
        <?= $tema['css_inline'] ?>
        
        /* Overrides específicos desta proposta */
        <?php if (!empty($proposta['cor_personalizada'])): ?>
            :root {
                --brand: <?= $proposta['cor_personalizada'] ?>;
            }
        <?php endif; ?>
    </style>
</head>
<body>

    <!-- Botão Imprimir -->
    <button class="btn-fab no-print" onclick="window.print()" title="Salvar como PDF">🖨️</button>

    <div class="page">
        
        <?php 
        // Inclui header específico do tema
        $header_file = __DIR__ . '/' . $tema['header_template'];
        if (file_exists($header_file)) {
            include $header_file;
        } else {
            // Header fallback
            include __DIR__ . '/header-classico.php';
        }
        ?>
        
        <!-- DADOS DO CLIENTE -->
        <div class="dados-grid">
            <div class="dados-cliente">
                <h2>Dados do Cliente</h2>
                <div class="info-row">
                    <span class="info-label">Nome:</span>
                    <span class="info-value"><?= htmlspecialchars($proposta['cliente_nome']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">E-mail:</span>
                    <span class="info-value"><?= htmlspecialchars($proposta['cliente_email']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Telefone:</span>
                    <span class="info-value">
                        <?= htmlspecialchars($proposta['cliente_telefone']) ?>
                        <?= !empty($proposta['cliente_whatsapp']) ? ' / Whats: ' . htmlspecialchars($proposta['cliente_whatsapp']) : '' ?>
                    </span>
                </div>
            </div>
            
            <div class="dados-obra">
                <h2>Local da Obra</h2>
                <div class="info-row">
                    <span class="info-label">Endereço:</span>
                    <span class="info-value"><?= htmlspecialchars($proposta['obra_endereco']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Bairro:</span>
                    <span class="info-value"><?= htmlspecialchars($proposta['obra_bairro']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cidade/UF:</span>
                    <span class="info-value"><?= htmlspecialchars($proposta['obra_cidade']) ?> - <?= htmlspecialchars($proposta['obra_uf']) ?></span>
                </div>
                <?php if (!empty($proposta['obra_area'])): ?>
                <div class="info-row">
                    <span class="info-label">Área:</span>
                    <span class="info-value"><?= htmlspecialchars($proposta['obra_area']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- CONTEÚDO DINÂMICO -->
        <div class="conteudo-blocos">
            <?php 
            // Blocos vêm do CRM - cada um é uma seção
            $blocos = $proposta['blocos'] ?? [];
            
            $numero_bloco = 1;
            foreach ($blocos as $bloco): 
            ?>
                <div class="bloco-secao">
                    <h2><?= $numero_bloco ?>. <?= htmlspecialchars($bloco['titulo']) ?></h2>
                    <div class="bloco-conteudo">
                        <?= $bloco['conteudo'] // HTML permitido do editor ?>
                    </div>
                </div>
            <?php 
                $numero_bloco++;
            endforeach; 
            ?>
        </div>

        <!-- INVESTIMENTO -->
        <div class="bloco-secao">
            <h2><?= $numero_bloco ?>. Investimento</h2>
            <p>O valor total para execução dos serviços descritos, incluindo equipe técnica, equipamentos, deslocamento e impostos, é de:</p>
            
            <div class="box-investimento">
                <div class="valor">R$ <?= number_format($proposta['valor'], 2, ',', '.') ?></div>
                <div class="extenso">(<?= $proposta['valor_extenso'] ?>)</div>
            </div>
            
            <?php if (!empty($proposta['observacao_valor'])): ?>
            <p><em><?= htmlspecialchars($proposta['observacao_valor']) ?></em></p>
            <?php endif; ?>
        </div>
        <?php $numero_bloco++; ?>

        <!-- CONDIÇÕES DE PAGAMENTO -->
        <div class="bloco-secao">
            <h2><?= $numero_bloco ?>. Condições de Pagamento</h2>
            <table class="tabela-prazos">
                <tr>
                    <th>Etapa</th>
                    <th>Percentual</th>
                    <th>Valor</th>
                    <th>Condição</th>
                </tr>
                <?php foreach ($proposta['parcelas'] as $parcela): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($parcela['descricao']) ?></strong></td>
                    <td><?= $parcela['percentual'] ?>%</td>
                    <td>R$ <?= number_format($parcela['valor'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($parcela['condicao']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <h3>Dados Bancários:</h3>
            <ul class="dados-bancarios">
                <li><strong>Banco:</strong> <?= htmlspecialchars($banco['nome']) ?></li>
                <li><strong>Agência:</strong> <?= htmlspecialchars($banco['agencia']) ?> | <strong>Conta:</strong> <?= htmlspecialchars($banco['conta']) ?></li>
                <li><strong>Titular:</strong> <?= htmlspecialchars($empresa['nome']) ?> | <strong>CNPJ:</strong> <?= htmlspecialchars($empresa['cnpj']) ?></li>
                <li><strong>PIX:</strong> <?= htmlspecialchars($banco['pix']) ?></li>
            </ul>
        </div>
        <?php $numero_bloco++; ?>

        <!-- EQUIPAMENTOS (se houver) -->
        <?php if (!empty($proposta['equipamentos'])): ?>
        <div class="bloco-secao">
            <h2><?= $numero_bloco ?>. Equipamentos Previstos</h2>
            <p>Para garantir a acurácia descrita nesta proposta, utilizaremos:</p>
            <ul class="lista-equipamentos">
                <?php foreach ($proposta['equipamentos'] as $equip): ?>
                <li><strong><?= htmlspecialchars($equip['nome']) ?>:</strong> <?= htmlspecialchars($equip['descricao']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php $numero_bloco++; ?>
        <?php endif; ?>

        <!-- CONSIDERAÇÕES FINAIS -->
        <div class="bloco-secao">
            <h2><?= $numero_bloco ?>. Considerações Finais</h2>
            <p>Esta proposta tem validade de <?= $proposta['validade_dias'] ?? 15 ?> dias. A <strong><?= htmlspecialchars($empresa['nome']) ?></strong> coloca-se à disposição para sanar quaisquer dúvidas técnicas.</p>
            <p>Garantimos que o produto final entregue será uma ferramenta robusta para o desenvolvimento do seu projeto.</p>
        </div>

        <!-- FOOTER -->
        <footer class="footer-proposta">
            <p>Atenciosamente,</p>
            <div class="linha-assinatura"></div>
            <p class="footer-empresa"><?= htmlspecialchars($empresa['nome']) ?></p>
            <p class="footer-info">
                <?= htmlspecialchars($empresa['endereco']) ?> • 
                <?= htmlspecialchars($empresa['telefone']) ?> • 
                <?= htmlspecialchars($empresa['email']) ?>
            </p>
        </footer>

    </div>

</body>
</html>
