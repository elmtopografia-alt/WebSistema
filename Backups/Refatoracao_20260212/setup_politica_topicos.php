<?php

/**
 * setup_politica_topicos.php
 * 
 * Define a estrutura oficial dos tópicos da proposta.
 * ALINHADO COM O TEXTO REAL DO DOCUMENTO DE PROPOSTA FORNECIDO PELO USUÁRIO.
 * 
 * ATUALIZAÇÃO: Executa em AMBOS os ambientes (Produção e Demo)
 */

require_once 'vendor/autoload.php';
require_once 'db.php';

echo "<pre>\n";
echo "=== ATUALIZAÇÃO DE ESTRUTURA PARA HTML PREMIUM ===\n\n";

// Define os ambientes a processar
$ambientes = [
    'PRODUCAO' => Database::getProd(),
    'DEMONSTRACAO' => Database::getDemo()
];

$estrutura = [
    // ===== CABEÇALHOS =====
    [ 'order' => 0, 'slug' => 'cabecalho', 'name' => 'Cabeçalho', 'category' => 'layout', 'is_required' => true, 'allowed_vars' => ['Cidade', 'DExrenso'], 'default_text' => '' ],
    
    // ===== CONTEÚDO =====
    [ 'order' => 1, 'slug' => 'dados_cliente', 'name' => 'Dados do Cliente', 'category' => 'presentation', 'is_required' => true, 'allowed_vars' => ['nome_cliente_salvo', 'email_salvo', 'telefone_salvo'], 'default_text' => '' ],
    [ 'order' => 2, 'slug' => 'local_obra', 'name' => 'Local da Obra', 'category' => 'presentation', 'is_required' => true, 'allowed_vars' => ['endereco_obra', 'area_obra'], 'default_text' => '' ],
    
    [ 
        'order' => 3, 'slug' => 'apresentacao', 'name' => '1. Apresentação', 'category' => 'presentation', 'is_required' => true, 'allowed_vars' => ['Empresa'], 
        'default_text' => '<p>A <strong>${Empresa}</strong> é referência em serviços topográficos de alta precisão. Com vasta experiência e histórico sólido de projetos concluídos, garantimos segurança e exatidão em cada medição.</p><p>Nosso compromisso é assegurar a conformidade das medidas reais do terreno com a documentação legal e a realidade física.</p>' 
    ],
    
    [ 'order' => 4, 'slug' => 'finalidade', 'name' => '2. Finalidade', 'category' => 'technical', 'is_required' => true, 'allowed_vars' => [], 'default_text' => '<p>O objetivo desta proposta é apresentar os custos e condições técnicas para a realização de serviços de topografia, visando atender às necessidades específicas do cliente com o mais alto padrão de qualidade.</p>' ],
    
    [ 
        'order' => 5, 'slug' => 'escopo', 'name' => '3. Escopo do Serviço', 'category' => 'technical', 'is_required' => true, 'allowed_vars' => ['escopo_servico'], 
        'default_text' => '<p>O serviço contempla todas as atividades de campo e escritório necessárias para a execução do levantamento topográfico conforme especificações técnicas e normas vigentes.</p><h4>Detalhamento das Atividades:</h4><ul><li>Levantamento perimétrico do imóvel;</li><li>Coleta de pontos cotados para altimetria;</li><li>Cadastro de elementos físicos;</li><li>Processamento dos dados brutos e geração de desenhos técnicos.</li></ul>' 
    ],
    
    [ 
        'order' => 6, 'slug' => 'documentacao', 'name' => '4. Documentação Gerada', 'category' => 'technical', 'is_required' => true, 'allowed_vars' => [], 
        'default_text' => '<p>Ao final dos serviços, serão entregues os seguintes documentos técnicos:</p><ul><li><strong>Planta Topográfica</strong> em formato digital (DWG) e plotagem (PDF);</li><li><strong>Memorial Descritivo</strong> detalhado do terreno;</li><li><strong>ART (Anotação de Responsabilidade Técnica)</strong> registrada no CREA;</li><li>Relatório Fotográfico e Técnico (quando aplicável).</li></ul>' 
    ],
    
    [ 
        'order' => 7, 'slug' => 'metodologia', 'name' => '5. Metodologia', 'category' => 'technical', 'is_required' => true, 'allowed_vars' => [], 
        'default_text' => '<h4>Etapa 1: Geodésia e Amarração (GPS/GNSS)</h4><p>Implantação da Rede de Apoio Geodésica com rastreadores GNSS de dupla frequência para georreferenciamento ao Sistema Geodésico Brasileiro (SIRGAS2000).</p><h4>Etapa 2: Topografia de Detalhe (Estação Total)</h4><p>Levantamento irradiado de todos os detalhes internos e externos listados no escopo e coleta da altimetria para geração do modelo digital do terreno.</p><h4>Etapa 3: Escritório</h4><p>Processamento dos vetores GNSS, unificação dos dados com a topografia clássica.</p><p><strong>Normas Técnicas Aplicáveis:</strong></p><ul><li>ABNT NBR 13.133 (Execução de Levantamento Topográfico)</li><li>Sistema de Referência: SIRGAS2000 UTM</li></ul>' 
    ],
    
    [ 
        'order' => 8, 'slug' => 'equipamentos', 'name' => '6. Equipamentos', 'category' => 'technical', 'is_required' => true, 'allowed_vars' => ['Veiculo', 'Estacao_Total', 'Drone'], 
        'default_text' => '<p>Utilizaremos equipamentos de última geração, calibrados e certificados:</p><ul><li><strong>Veículo:</strong> ${Veiculo}</li><li><strong>Estação Total:</strong> ${Estacao_Total}</li><li><strong>GPS/GNSS:</strong> Receptores de Alta Precisão (RTK/Pós-Processado)</li><li><strong>Drone (VANT):</strong> ${Drone}</li></ul>' 
    ],

    [ 'order' => 9, 'slug' => 'investimento', 'name' => '7. Investimento', 'category' => 'financial', 'is_required' => true, 'allowed_vars' => ['ValorProposta', 'ValorExtenso'], 'default_text' => '' ],
    [ 'order' => 10, 'slug' => 'condicoes_pagamento', 'name' => '8. Condições de Pagamento', 'category' => 'financial', 'is_required' => true, 'allowed_vars' => ['prazo_execucao', 'mobilizacao_valor'], 'default_text' => '' ],
    [ 'order' => 11, 'slug' => 'dados_bancarios', 'name' => '9. Dados Bancários', 'category' => 'financial', 'is_required' => true, 'allowed_vars' => ['Banco', 'Empresa'], 'default_text' => '' ],
    
    [ 
        'order' => 12, 'slug' => 'consideracoes', 'name' => '10. Considerações Finais', 'category' => 'legal', 'is_required' => true, 'allowed_vars' => ['Empresa'], 
        'default_text' => '<p>Agradecemos a oportunidade de apresentar nossa proposta. Temos a certeza de que nossa solução técnica fornecerá a base exata necessária para o desenvolvimento do seu projeto.</p><p>Permanecemos à disposição para esclarecimentos adicionais e negociação das condições comerciais.</p><br><p class="closing">Atenciosamente,</p>' 
    ],

    // ===== RODAPÉ =====
    [ 'order' => 99, 'slug' => 'rodape', 'name' => 'Rodapé', 'category' => 'layout', 'is_required' => true, 'allowed_vars' => ['Empresa', 'CNPJ'], 'default_text' => '${Empresa} • CNPJ: ${CNPJ} • WhatsApp: ${whatsapp}' ]
];


foreach ($ambientes as $nomeAmbiente => $conn) {
    if (!$conn) {
        echo "⚠️  PULANDO {$nomeAmbiente}: Conexão falhou.\n";
        continue;
    }
    
    echo "-------------------------------------------------------\n";
    echo "🔄 PROCESSANDO AMBIENTE: {$nomeAmbiente}\n";
    echo "-------------------------------------------------------\n";
    
    // 0. Garantir coluna 'order'
    try {
        $result = $conn->query("SHOW COLUMNS FROM proposal_block_templates LIKE 'order'");
        if ($result && $result->num_rows == 0) {
            $conn->query("ALTER TABLE proposal_block_templates ADD COLUMN `order` INT DEFAULT 0");
            echo "  ✅ Coluna 'order' criada.\n";
        }
    } catch (Exception $e) { echo "  ⚠️  Erro check coluna: " . $e->getMessage() . "\n"; }

    // 1. Limpeza
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("TRUNCATE TABLE proposal_content_variations");
    $conn->query("TRUNCATE TABLE proposal_block_templates");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "  ✅ Tabelas limpas.\n";

    // 2. Inserção
    $stmtBlock = $conn->prepare("INSERT INTO proposal_block_templates (slug, name, category, default_content_json, is_active, `order`) VALUES (?, ?, ?, ?, 1, ?)");
    $stmtContent = $conn->prepare("INSERT INTO proposal_content_variations (block_slug, variation_name, content_text, is_default) VALUES (?, 'Texto Padrão', ?, 1)");

    foreach ($estrutura as $item) {
        $jsonConfig = json_encode(['level' => 'root', 'is_required' => $item['is_required'], 'allowed_vars' => $item['allowed_vars']], JSON_UNESCAPED_UNICODE);
        
        $stmtBlock->bind_param("ssssi", $item['slug'], $item['name'], $item['category'], $jsonConfig, $item['order']);
        if ($stmtBlock->execute()) {
           // echo "  ✓ Bloco '{$item['name']}' inserido.\n";
        } else {
            echo "  ✗ ERRO Bloco '{$item['name']}': {$conn->error}\n";
        }
        
        if (!empty($item['default_text'])) {
            $stmtContent->bind_param("ss", $item['slug'], $item['default_text']);
            $stmtContent->execute();
        }
    }
    
    echo "  ✅ Importação concluída para {$nomeAmbiente}.\n\n";
    
    $stmtBlock->close();
    $stmtContent->close();
}

echo "\n🏁 TODOS OS AMBIENTES FINALIZADOS.\n";
echo "</pre>";
