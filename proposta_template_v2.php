<?php
/**
 * Template de Proposta Técnica - GeoMetrópole
 * Isolado do CSS do sistema legado para evitar conflitos
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta Técnica - GeoMetrópole</title>
    <style>
        /* CSS Reset Isolado - Não conflita com seu sistema legado */
        #proposta-container {
            all: initial;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        #proposta-container * {
            all: revert;
            box-sizing: border-box;
        }
        
        /* Cabeçalho Profissional */
        .proposta-header {
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .logo-area {
            flex: 1;
        }
        
        .logo-area h1 {
            color: #1e3a8a;
            font-size: 28px;
            margin: 0 0 5px 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .logo-area .tagline {
            color: #64748b;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .proposta-meta {
            text-align: right;
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 8px;
            border-left: 4px solid #1e3a8a;
        }
        
        .proposta-numero {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 5px;
        }
        
        .proposta-data {
            color: #64748b;
            font-size: 14px;
        }
        
        /* Seções */
        .secao {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .secao-titulo {
            color: #1e3a8a;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .secao-numero {
            background: #1e3a8a;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        /* Dados do Cliente - Layout em Grid */
        .dados-cliente {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .campo-dado {
            margin-bottom: 10px;
        }
        
        .campo-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        
        .campo-valor {
            font-size: 15px;
            color: #1e293b;
            font-weight: 500;
        }
        
        /* Local da Obra - Destaque Visual */
        .local-obra {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .local-obra h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            opacity: 0.9;
        }
        
        .local-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .local-item {
            background: rgba(255,255,255,0.1);
            padding: 10px 15px;
            border-radius: 6px;
            backdrop-filter: blur(10px);
        }
        
        .local-label {
            font-size: 11px;
            opacity: 0.8;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        /* TABELA DE EQUIPAMENTOS - BLOCO 6 (O CRÍTICO) */
        .tabela-equipamentos-licitacao {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .tabela-equipamentos-licitacao thead {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
        }
        
        .tabela-equipamentos-licitacao th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .tabela-equipamentos-licitacao tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        
        .tabela-equipamentos-licitacao tbody tr:hover {
            background-color: #f1f5f9;
            transform: scale(1.01);
        }
        
        .tabela-equipamentos-licitacao tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .tabela-equipamentos-licitacao td {
            padding: 15px 12px;
            vertical-align: middle;
        }
        
        .destaque-equipamento {
            font-weight: 700;
            color: #1e40af;
            font-size: 15px;
            background: #dbeafe;
            padding: 8px 12px;
            border-radius: 6px;
            display: inline-block;
            border-left: 3px solid #2563eb;
        }
        
        /* Cronograma */
        .cronograma-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .cronograma-table th {
            background: #f1f5f9;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #cbd5e1;
        }
        
        .cronograma-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .cronograma-table tr.total {
            background: #1e3a8a;
            color: white;
            font-weight: bold;
        }
        
        /* Investimento - Destaque */
        .box-investimento {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
            box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.3);
        }
        
        .investimento-label {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .investimento-valor {
            font-size: 42px;
            font-weight: 800;
            margin: 10px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .investimento-extenso {
            font-size: 16px;
            opacity: 0.95;
            font-style: italic;
        }
        
        /* Condições de Pagamento */
        .pagamento-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 0;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .pagamento-header {
            background: #1e3a8a;
            color: white;
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .pagamento-row {
            display: contents;
        }
        
        .pagamento-row > div {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            background: white;
        }
        
        .pagamento-row:nth-child(even) > div {
            background: #f8fafc;
        }
        
        /* Dados Bancários */
        .dados-bancarios {
            background: #fef3c7;
            border: 2px dashed #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .dados-bancarios h4 {
            color: #92400e;
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .banco-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .banco-item {
            display: flex;
            flex-direction: column;
        }
        
        .banco-label {
            font-size: 12px;
            color: #92400e;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .banco-valor {
            font-size: 16px;
            color: #78350f;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        /* Assinatura */
        .assinatura-area {
            margin-top: 60px;
            text-align: center;
            page-break-inside: avoid;
        }
        
        .linha-assinatura {
            width: 300px;
            border-top: 2px solid #1e3a8a;
            margin: 0 auto 10px auto;
            padding-top: 10px;
        }
        
        /* Utilitários */
        .texto-justificado {
            text-align: justify;
            line-height: 1.8;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-sucesso { background: #dcfce7; color: #166534; }
        .badge-aviso { background: #fef3c7; color: #92400e; }
        
        /* Print Optimization */
        @media print {
            #proposta-container {
                box-shadow: none;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .secao {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div id="proposta-container">
    
    <!-- CABEÇALHO -->
    <div class="proposta-header">
        <div class="logo-area">
            <h1>GeoMetrópole</h1>
            <div class="tagline">Engenharia e Topografia Ltda.</div>
            <div style="margin-top: 10px; font-size: 13px; color: #64748b;">
                Levantamentos Topográficos de Alta Precisão
            </div>
        </div>
        <div class="proposta-meta">
            <div class="proposta-numero">Nº <?php echo $numero_proposta; ?></div>
            <div class="proposta-data">Belo Horizonte, <?php echo $data_formatada; ?></div>
            <div style="margin-top: 10px; font-size: 12px; color: #64748b;">
                Proposta Técnica Comercial
            </div>
        </div>
    </div>

    <!-- DADOS DO CLIENTE -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">1</span>
            Dados do Cliente
        </div>
        <div class="dados-cliente">
            <div>
                <div class="campo-dado">
                    <div class="campo-label">Nome Completo</div>
                    <div class="campo-valor"><?php echo htmlspecialchars($cliente['nome']); ?></div>
                </div>
                <div class="campo-dado">
                    <div class="campo-label">E-mail</div>
                    <div class="campo-valor"><?php echo htmlspecialchars($cliente['email']); ?></div>
                </div>
            </div>
            <div>
                <div class="campo-dado">
                    <div class="campo-label">Telefone</div>
                    <div class="campo-valor"><?php echo htmlspecialchars($cliente['telefone']); ?></div>
                </div>
                <div class="campo-dado">
                    <div class="campo-label">WhatsApp</div>
                    <div class="campo-valor"><?php echo htmlspecialchars($cliente['whatsapp']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- LOCAL DA OBRA -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">2</span>
            Local da Obra
        </div>
        <div class="local-obra">
            <div class="local-grid">
                <div class="local-item">
                    <div class="local-label">Endereço</div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($obra['endereco']); ?></div>
                </div>
                <div class="local-item">
                    <div class="local-label">Bairro</div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($obra['bairro']); ?></div>
                </div>
                <div class="local-item">
                    <div class="local-label">Cidade/Estado</div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($obra['cidade_estado']); ?></div>
                </div>
                <div class="local-item">
                    <div class="local-label">Área Estimada</div>
                    <div style="font-weight: 600; font-size: 18px;"><?php echo htmlspecialchars($obra['area']); ?> m²</div>
                </div>
            </div>
        </div>
    </div>

    <!-- APRESENTAÇÃO -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">3</span>
            Apresentação
        </div>
        <?php echo $conteudo['apresentacao'] ?: '<p>Conteúdo não definido.</p>'; ?>
    </div>

    <!-- FINALIDADE -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">4</span>
            Finalidade do Serviço
        </div>
        <?php echo $conteudo['finalidade'] ?: '<p>Conteúdo não definido.</p>'; ?>
    </div>

    <!-- ESCOPO -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">5</span>
            Escopo do Serviço
        </div>
        <?php echo $conteudo['escopo'] ?: '<p>Conteúdo não definido.</p>'; ?>
    </div>

    <!-- EQUIPAMENTOS - BLOCO 6 CRÍTICO -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">6</span>
            Equipamentos Técnicos Específicos
        </div>
        
        <!-- AQUI ENTRA A TABELA DINÂMICA DE EQUIPAMENTOS -->
        <?php echo $tabela_equipamentos_html; ?>
    </div>

    <!-- METODOLOGIA -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">7</span>
            Metodologia de Execução
        </div>
        
        <?php echo $conteudo['metodologia'] ?: '<p>Conteúdo não definido.</p>'; ?>
    </div>

    <!-- CRONOGRAMA -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">8</span>
            Cronograma de Execução
        </div>
        <table class="cronograma-table">
            <thead>
                <tr>
                    <th>Etapa</th>
                    <th>Descrição Técnica</th>
                    <th>Prazo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cronograma_itens)): ?>
                    <?php foreach ($cronograma_itens as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nome_etapa'] ?? $item['titulo'] ?? 'Etapa'); ?></td>
                            <td><?php echo htmlspecialchars($item['descricao'] ?? $item['texto'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($item['prazo'] ?? $item['dias'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total">
                        <td colspan="2">TOTAL ESTIMADO</td>
                        <td><?php echo count($cronograma_itens); ?> etapas</td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center; color:#64748b; padding:20px;">
                            Cronograma não definido para esta proposta.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- INVESTIMENTO -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">9</span>
            Investimento
        </div>
        <?php if (!empty($conteudo['investimento'])): ?>
            <?php echo $conteudo['investimento']; ?>
        <?php else: ?>
            <div class="box-investimento">
                <div class="investimento-label">Valor Total da Proposta</div>
                <div class="investimento-valor">R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></div>
                <div class="investimento-extenso">(<?php echo $valor_extenso; ?>)</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- CONDIÇÕES DE PAGAMENTO -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">10</span>
            Condições de Pagamento
        </div>
        <?php if (!empty($conteudo['condicoes_pagamento'])): ?>
             <?php echo $conteudo['condicoes_pagamento']; ?>
        <?php else: ?>
            <div class="pagamento-grid">
                <div class="pagamento-header">Etapa</div>
                <div class="pagamento-header">%</div>
                <div class="pagamento-header">Valor</div>
                
                <div class="pagamento-row">
                    <div>Mobilização (Aceite da Proposta)</div>
                    <div>30%</div>
                    <div>R$ <?php echo number_format($valor_total * 0.3, 2, ',', '.'); ?></div>
                </div>
                
                <div class="pagamento-row">
                    <div>Entrega Final (Após aprovação)</div>
                    <div>70%</div>
                    <div>R$ <?php echo number_format($valor_total * 0.7, 2, ',', '.'); ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- DADOS BANCÁRIOS -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">11</span>
            Dados para Pagamento
        </div>
        <?php if (!empty($conteudo['dados_bancarios'])): ?>
            <?php echo $conteudo['dados_bancarios']; ?>
        <?php else: ?>
            <div class="dados-bancarios">
                <h4>🏦 Transferência Bancária</h4>
                <div class="banco-info">
                    <div class="banco-item">
                        <span class="banco-label">Banco</span>
                        <span class="banco-valor">Itaú Unibanco S.A.</span>
                    </div>
                    <div class="banco-item">
                        <span class="banco-label">Agência / Conta</span>
                        <span class="banco-valor">2934 / 56789-0</span>
                    </div>
                    <div class="banco-item" style="grid-column: 1 / -1;">
                        <span class="banco-label">PIX (Chave E-mail)</span>
                        <span class="banco-valor">financeiro@geometropolesp.com</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- CONSIDERAÇÕES FINAIS -->
    <div class="secao">
        <div class="secao-titulo">
            <span class="secao-numero">12</span>
            Considerações Finais
        </div>
        <?php echo $conteudo['consideracoes_finais'] ?: $conteudo['consideracoes'] ?: '<p>Conteúdo não definido.</p>'; ?>
    </div>

    <!-- ASSINATURA -->
    <div class="assinatura-area">
        <div class="linha-assinatura">
            Atenciosamente,<br>
            <strong>GeoMetrópole Engenharia e Topografia Ltda.</strong>
        </div>
        <div style="font-size: 13px; color: #64748b; margin-top: 10px;">
            Belo Horizonte - MG • CNPJ: XX.XXX.XXX/0001-XX<br>
            CREA-MG: XXXXX • Inscrição Municipal: XXXXX
        </div>
    </div>

    <!-- BOTÕES DE AÇÃO (não imprimem) -->
    <div class="no-print" style="margin-top: 40px; text-align: center; padding: 20px; background: #f1f5f9; border-radius: 8px;">
        <button onclick="window.print()" style="background: #1e3a8a; color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 0 10px;">
            🖨️ Imprimir / Salvar PDF
        </button>
        <button onclick="history.back()" style="background: #64748b; color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 0 10px;">
            ← Voltar
        </button>
    </div>

</div>

</body>
</html>
