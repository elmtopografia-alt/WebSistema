<?php
/**
 * povoar_modelos_premium.php
 * SGT Propostas - Injeção de Modelos de Alta Performance
 */

require_once __DIR__ . '/ConnectionManager.php';

header('Content-Type: text/plain; charset=utf-8');

$servicosIds = [
    'producao' => [
        'usucapiao' => 11,
        'drone' => 19,
        'georreferenciamento' => 25
    ],
    'demo' => [
        'usucapiao' => 11,
        'drone' => 19,
        'georreferenciamento' => 22
    ]
];

// Dados dos Blocos (Padrão 14 Pontos SGT)
$modelos = [
    'usucapiao' => [
        'title' => 'Levantamento para Usucapião',
        'blocks' => [
            ['slug' => 'cabecalho', 'title' => '1. Cabeçalho', 'cat' => 'layout', 'order' => 10, 'content' => ""],
            ['slug' => 'dados_cliente', 'title' => '2. Dados do Cliente', 'cat' => 'presentation', 'order' => 20, 'content' => ""],
            ['slug' => 'local_obra', 'title' => '3. Dados da Obra', 'cat' => 'presentation', 'order' => 30, 'content' => ""],
            ['slug' => 'finalidade', 'title' => '4. Finalidade', 'cat' => 'presentation', 'order' => 40, 'content' => "A elaboração precisa das peças técnicas (plantas e memoriais) serve como base física inquestionável para o processo de Usucapião."],
            ['slug' => 'escopo', 'title' => '5. Escopo', 'cat' => 'technical', 'order' => 50, 'content' => "- Medição planimétrica do perímetro;\n- Identificação de confrontantes;\n- Emissão de ART técnica."],
            ['slug' => 'metodologia', 'title' => '6. Metodologia', 'cat' => 'technical', 'order' => 60, 'content' => "Levantamento via GPS Geodésico RTK e Estação Total para fechamento de poligonais."],
            ['slug' => 'entregaveis', 'title' => '7. Entregáveis', 'cat' => 'technical', 'order' => 70, 'content' => "- Planta Topográfica (PDF/DWG);\n- Memorial Descritivo assinado;\n- Guia de ART paga."],
            ['slug' => 'equipamentos', 'title' => '8. Equipamentos Previstos', 'cat' => 'technical', 'order' => 80, 'content' => "Receptores GNSS L1/L2 e Estação Total de alta precisão."],
            ['slug' => 'prazos', 'title' => '9. Prazos', 'cat' => 'technical', 'order' => 90, 'content' => "Execução em \${prazo_execucao} dias úteis."],
            ['slug' => 'investimento', 'title' => '10. Investimento', 'cat' => 'financial', 'order' => 100, 'content' => "**\${ValorProposta}** (\${ValorExtenso})"],
            ['slug' => 'condicoes_pagamento', 'title' => '11. Condições Pagamento', 'cat' => 'financial', 'order' => 110, 'content' => "50% Sinal | 50% Entrega"],
            ['slug' => 'dados_bancarios', 'title' => '12. Dados Bancários', 'cat' => 'financial', 'order' => 120, 'content' => "Favorecido: \${Empresa}\nPIX: \${PIX}"],
            ['slug' => 'consideracoes', 'title' => '13. Considerações Finais', 'cat' => 'legal', 'order' => 130, 'content' => "Proposta válida por 15 dias."],
            ['slug' => 'assinatura', 'title' => '14. Assinatura', 'cat' => 'layout', 'order' => 140, 'content' => ""]
        ]
    ],
    'georreferenciamento' => [
        'title' => 'Georreferenciamento INCRA',
        'blocks' => [
            ['slug' => 'cabecalho', 'title' => '1. Cabeçalho', 'cat' => 'layout', 'order' => 10, 'content' => ""],
            ['slug' => 'dados_cliente', 'title' => '2. Dados do Cliente', 'cat' => 'presentation', 'order' => 20, 'content' => ""],
            ['slug' => 'local_obra', 'title' => '3. Dados da Obra', 'cat' => 'presentation', 'order' => 30, 'content' => ""],
            ['slug' => 'finalidade', 'title' => '4. Finalidade', 'cat' => 'presentation', 'order' => 40, 'content' => "Certificação de imóvel rural junto ao INCRA (SIGEF) conforme Lei 10.267."],
            ['slug' => 'escopo', 'title' => '5. Escopo', 'cat' => 'technical', 'order' => 50, 'content' => "Levantamento, análise de confrontações e submissão ao SIGEF."],
            ['slug' => 'metodologia', 'title' => '6. Metodologia', 'cat' => 'technical', 'order' => 60, 'content' => "Rastreio GNSS Estático/RTK amarrado ao SIRGAS 2000."],
            ['slug' => 'entregaveis', 'title' => '7. Entregáveis', 'cat' => 'technical', 'order' => 70, 'content' => "Certificado do SIGEF, Planta e Memorial."],
            ['slug' => 'equipamentos', 'title' => '8. Equipamentos Previstos', 'cat' => 'technical', 'order' => 80, 'content' => "GPS Geodésico \${GPS}."],
            ['slug' => 'prazos', 'title' => '9. Prazos', 'cat' => 'technical', 'order' => 90, 'content' => "Conforme contrato."],
            ['slug' => 'investimento', 'title' => '10. Investimento', 'cat' => 'financial', 'order' => 100, 'content' => "**\${ValorProposta}**"],
            ['slug' => 'condicoes_pagamento', 'title' => '11. Condições Pagamento', 'cat' => 'financial', 'order' => 110, 'content' => ""],
            ['slug' => 'dados_bancarios', 'title' => '12. Dados Bancários', 'cat' => 'financial', 'order' => 120, 'content' => ""],
            ['slug' => 'consideracoes', 'title' => '13. Considerações Finais', 'cat' => 'legal', 'order' => 130, 'content' => ""],
            ['slug' => 'assinatura', 'title' => '14. Assinatura', 'cat' => 'layout', 'order' => 140, 'content' => ""]
        ]
    ],
    'drone' => [
        'title' => 'Aerofotogrametria com Drone',
        'blocks' => [
            ['slug' => 'cabecalho', 'title' => '1. Cabeçalho', 'cat' => 'layout', 'order' => 10, 'content' => ""],
            ['slug' => 'dados_cliente', 'title' => '2. Dados do Cliente', 'cat' => 'presentation', 'order' => 20, 'content' => ""],
            ['slug' => 'local_obra', 'title' => '3. Dados da Obra', 'cat' => 'presentation', 'order' => 30, 'content' => ""],
            ['slug' => 'finalidade', 'title' => '4. Finalidade', 'cat' => 'presentation', 'order' => 40, 'content' => "Mapeamento aéreo detalhado para projetos de engenharia."],
            ['slug' => 'escopo', 'title' => '5. Escopo', 'cat' => 'technical', 'order' => 50, 'content' => "Voo autônomo, processamento e vetorização."],
            ['slug' => 'metodologia', 'title' => '6. Metodologia', 'cat' => 'technical', 'order' => 60, 'content' => "Processamento digital de imagens (SfM)."],
            ['slug' => 'entregaveis', 'title' => '7. Entregáveis', 'cat' => 'technical', 'order' => 70, 'content' => "Ortomosaico, MDT e Curvas de Nível."],
            ['slug' => 'equipamentos', 'title' => '8. Equipamentos Previstos', 'cat' => 'technical', 'order' => 80, 'content' => "VANT \${Drone}."],
            ['slug' => 'prazos', 'title' => '9. Prazos', 'cat' => 'technical', 'order' => 90, 'content' => "\${dias_campo} dias campo / \${dias_escritorio} dias escritório."],
            ['slug' => 'investimento', 'title' => '10. Investimento', 'cat' => 'financial', 'order' => 100, 'content' => "**\${ValorProposta}**"],
            ['slug' => 'condicoes_pagamento', 'title' => '11. Condições Pagamento', 'cat' => 'financial', 'order' => 110, 'content' => ""],
            ['slug' => 'dados_bancarios', 'title' => '12. Dados Bancários', 'cat' => 'financial', 'order' => 120, 'content' => ""],
            ['slug' => 'consideracoes', 'title' => '13. Considerações Finais', 'cat' => 'legal', 'order' => 130, 'content' => ""],
            ['slug' => 'assinatura', 'title' => '14. Assinatura', 'cat' => 'layout', 'order' => 140, 'content' => ""]
        ]
    ]
];

foreach (['producao', 'demo'] as $env) {
    echo "=== Processando Ambiente: $env ===\n";
    try {
        $conn = ConnectionManager::get($env);
        
        foreach ($modelos as $key => $mod) {
            $servicoId = $servicosIds[$env][$key] ?? null;
            if (!$servicoId) {
                echo "⚠️ Pulando $key: Serviço não mapeado em $env.\n";
                continue;
            }

            // Limpa blocos antigos do serviço para evitar duplicatas (Injeção limpa)
            $conn->query("DELETE FROM service_type_blocks WHERE service_type_id = $servicoId");
            echo "🧹 Limpando blocos de $key (ID $servicoId)...\n";

            foreach ($mod['blocks'] as $b) {
                $slug = $b['slug'];
                $title = $b['title'];
                $cat = $b['cat'];
                $order = $b['order'];
                $content = $conn->real_escape_string($b['content']);
                $required = ($slug == 'cabecalho' || $slug == 'rodape') ? 0 : 1;

                $sql = "INSERT INTO service_type_blocks (service_type_id, block_slug, block_title, category, display_order, default_content, is_required, is_active) 
                        VALUES ($servicoId, '$slug', '$title', '$cat', $order, '$content', $required, 1)";
                
                if ($conn->query($sql)) {
                    echo "  ✅ Bloco [$slug] inserido.\n";
                } else {
                    echo "  ❌ Erro no bloco [$slug]: " . $conn->error . "\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "❌ Erro FATAL no ambiente $env: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "=== OPERAÇÃO CONCLUÍDA ===";
