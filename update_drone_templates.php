<?php
/**
 * update_drone_templates.php
 * Atualiza os textos padrão para o serviço de Drone (Levantamento Fotogramétrico)
 * Baseado na solicitação do usuário.
 */

require_once 'db.php';

// TEXTOS FORNECIDOS PELO USUÁRIO (COM PLACEHOLDERS REAIS)
$txtApresentacao = "<p>A <strong>\${Empresa}</strong> apresenta esta proposta técnica para execução de Levantamento Planialtimétrico via Aerofotogrametria com Drones (VANTs).</p>
<p>Trata-se de Engenharia de Precisão, não simples filmagem aérea. Geramos representação digital fiel do terreno com coordenadas exatas (Latitude, Longitude, Altitude) para base legal de projetos de arquitetura, loteamentos, regularização e cálculo de volumes.</p>";

$txtEscopo = "<p><strong>Levantamento Fotogramétrico com Drone</strong></p>
<p>Execução de levantamento na área de <strong>\${AreaEstimada}</strong>, localizada em <strong>\${ClienteCidadeUF}</strong>, com as seguintes características:</p>
<ul>
<li>Tipo de Terreno: <strong>\${TipoTerreno}</strong>;</li>
<li>Cobertura Vegetal: <strong>\${CoberturaVegetal}</strong>;</li>
<li>Acesso: <strong>\${AcessoLocal}</strong>;</li>
<li>Restrições: <strong>\${RestricoesAereas}</strong>.</li>
</ul>
<p>O serviço compreende:</p>
<ul>
<li>Planejamento de voo e estudo de viabilidade aérea (consulta DECEA para \${ClienteCidadeUF});</li>
<li>Implantação de Pontos de Controle Terrestre (GCPs) com GPS RTK;</li>
<li>Captura de imagens aéreas com sobreposição adequada (80% longitudinal, 70% lateral);</li>
<li>Processamento fotogramétrico e geração de ortomosaico georreferenciado;</li>
<li>Geração de MDT (Modelo Digital do Terreno) e MDS (Modelo Digital de Superfície);</li>
<li>Extração de curvas de nível e elementos vetoriais;</li>
<li>Cálculo de volumes (quando solicitado).</li>
</ul>";

$txtMetodologia = "<p><strong>Etapa 1: Planejamento e Análise de Viabilidade</strong></p>
<p>Análise do local em \${endereco_obra}, \${bairro_obra}, \${cidade_obra} - \${estado_obra} considerando acesso \${AcessoLocal} e cobertura vegetal \${CoberturaVegetal}. Verificação de restrições aéreas junto ao DECEA para o local da obra.</p>

<p><strong>Etapa 2: Reconhecimento de Campo</strong></p>
<p>Visita técnica para avaliação das condições reais do terreno (\${TipoTerreno}) e instalação de alvos para GCPs em pontos estratégicos acessíveis.</p>

<p><strong>Etapa 3: Execução do Voo</strong></p>
<p>Checklist de segurança considerando condições meteorológicas e restrições (\${RestricoesAereas}). O drone executa rota autônoma programada.</p>

<p><strong>Etapa 4: Processamento Fotogramétrico</strong></p>
<p>Geração da nuvem de pontos densa, ortomosaico e modelos digitais em workstation dedicada.</p>

<p><strong>Etapa 5: Vetorização e Entrega</strong></p>
<p>Desenho técnico final com curvas de nível e elementos do terreno.</p>";

$txtInvestimento = "<p>O valor total para execução dos serviços descritos, incluindo equipe técnica, equipamentos (Drone, GPS RTK, Estação de Trabalho), deslocamento e impostos, é de:</p>
<h3 style='color:#000; text-align:center;'>\${ValorProposta} (\${ValorExtenso})</h3>
<p>Este investimento reflete o custo-benefício da tecnologia: maior riqueza de dados (milhões de pontos) em menor tempo de execução comparado à topografia tradicional.</p>";

$txtPagamento = "<ul>
<li>Mobilização (Sinal): <strong>\${mobilizacao_percentual}% – \${mobilizacao_valor}</strong> (No aceite da proposta).</li>
<li>Entrega Final: <strong>\${restante_percentual}% – \${restante_valor}</strong> (Na entrega dos arquivos digitais e físicos).</li>
</ul>";

$txtBancarios = "<ul>
<li>Banco: \${Banco}</li>
<li>Agência: \${Agencia}</li>
<li>Conta: \${Conta}</li>
<li>Titular: \${Empresa}</li>
<li>CNPJ: \${CNPJ}</li>
<li>Chave PIX: \${PIX}</li>
</ul>";

$txtConsideracoes = "<p>Agradecemos a oportunidade de apresentar nossa proposta técnica.</p>
<p>A Aerofotogrametria com Drones representa o estado da arte em levantamento topográfico, combinando rapidez, segurança e precisão.</p>
<p>Ressaltamos que a qualidade do produto final depende da colaboração do cliente no acesso à área e na liberação de voos. Permanecemos à disposição para esclarecimentos técnicos adicionais.</p>
<br><br>
<p style='text-align:center'>Atenciosamente</p>
<br>
<p style='text-align:center'>______________________________<br>
<strong>\${Empresa}</strong><br>
📞 \${whatsapp}</p>";


// LÓGICA DE ATUALIZAÇÃO
echo "<h1>Atualizando Textos Padrão (Drone)</h1>";

try {
    $conn = Database::getProd();
    
    // Identificar ID do serviço de Drone (Procura por 'Drone' ou 'Aerofotogrametria')
    $res = $conn->query("SELECT id_servico, nome FROM Tipo_Servicos WHERE nome LIKE '%Drone%' OR nome LIKE '%Aero%' LIMIT 1");
    $servico = $res->fetch_assoc();
    
    if (!$servico) {
        die("<h3 style='color:red'>Serviço de Drone não encontrado! Crie um serviço com 'Drone' no nome primeiro.</h3>");
    }
    
    $idServico = $servico['id_servico'];
    echo "<p>Serviço Alvo: <strong>{$servico['nome']} (ID: $idServico)</strong></p>";

    // Função auxiliar para atualizar blocos
    function updateBlock($conn, $idServico, $blockSlug, $content) {
        $table = "service_type_blocks";
        
        // Mapeamento de Títulos e Categorias (Baseado no slug)
        $meta = [
            'apresentacao' => ['title' => 'Apresentação', 'cat' => 'presentation', 'order' => 10],
            'escopo' => ['title' => 'Escopo dos Serviços', 'cat' => 'technical', 'order' => 20],
            'metodologia' => ['title' => 'Metodologia Executiva', 'cat' => 'technical', 'order' => 30],
            'cronograma' => ['title' => 'Cronograma e Investimento', 'cat' => 'financial', 'order' => 40],
            'condicoes_pagamento' => ['title' => 'Condições de Pagamento', 'cat' => 'financial', 'order' => 50],
            'dados_bancarios' => ['title' => 'Dados Bancários', 'cat' => 'financial', 'order' => 55],
            'consideracoes_finais' => ['title' => 'Considerações Finais', 'cat' => 'legal', 'order' => 60],
        ];

        $title = $meta[$blockSlug]['title'] ?? ucfirst($blockSlug);
        $cat = $meta[$blockSlug]['cat'] ?? 'technical';
        $order = $meta[$blockSlug]['order'] ?? 99;

        // Verifica se registro existe
        $check = $conn->query("SELECT id FROM $table WHERE service_type_id = $idServico AND block_slug = '$blockSlug'");
        
        if ($check && $check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE $table SET default_content = ? WHERE service_type_id = ? AND block_slug = ?");
            $stmt->bind_param("sis", $content, $idServico, $blockSlug);
            $stmt->execute();
            echo "<li>Block '$blockSlug': <span style='color:blue'>Atualizado</span></li>";
        } else {
            // INSERT completo com colunas obrigatórias
            $stmt = $conn->prepare("INSERT INTO $table (service_type_id, block_slug, block_title, category, display_order, is_required, default_content, is_active, allowed_vars) VALUES (?, ?, ?, ?, ?, 1, ?, 1, '[]')");
            $stmt->bind_param("isssis", $idServico, $blockSlug, $title, $cat, $order, $content);
            
            if ($stmt->execute()) {
                echo "<li>Block '$blockSlug': <span style='color:green'>Criado</span></li>";
            } else {
                 echo "<li>Block '$blockSlug': <span style='color:red'>Erro ao criar: " . $stmt->error . "</span></li>";
            }
        }
    }

    echo "<ul>";
    updateBlock($conn, $idServico, 'apresentacao', $txtApresentacao);
    updateBlock($conn, $idServico, 'escopo', $txtEscopo);
    updateBlock($conn, $idServico, 'metodologia', $txtMetodologia);
    updateBlock($conn, $idServico, 'cronograma', $txtInvestimento); // Usando cronograma/investimento como slot (se for separado, ajustar)
    // Se houver blocos específicos para Condições e Considerações no service_type_blocks:
    updateBlock($conn, $idServico, 'condicoes_pagamento', $txtPagamento);
    updateBlock($conn, $idServico, 'consideracoes_finais', $txtConsideracoes);
    // Dados Bancários geralmente vai dentro de "condicoes_pagamento" ou um bloco extra. Vamos concatenar ou criar novo.
    updateBlock($conn, $idServico, 'dados_bancarios', $txtBancarios);
    
    echo "</ul>";
    echo "<h3 style='color:green'>Sucesso! Textos atualizados.</h3>";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
