-- ARQUITETURA EXPLÍCITA DE PROPOSTAS - PART 1
-- 1. CRIA TABELA DE LIGAÇÃO
DROP TABLE IF EXISTS `service_type_blocks`;
CREATE TABLE IF NOT EXISTS `service_type_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_type_id` int(11) NOT NULL COMMENT 'FK para Tipo_Servicos.id_servico',
  `block_slug` varchar(50) NOT NULL COMMENT 'Identificador único do bloco',
  `block_title` varchar(100) NOT NULL COMMENT 'Título exibido no editor',
  `category` enum('layout','presentation','technical','financial','legal') DEFAULT 'technical',
  `display_order` int(11) DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 1,
  `default_content` longtext COMMENT 'Conteúdo HTML padrão',
  `allowed_vars` json COMMENT 'Variáveis permitidas [\"Empresa\",\"area_obra\",...]',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_service_block` (`service_type_id`, `block_slug`),
  KEY `idx_service_order` (`service_type_id`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SERVIÇO 11: USUCAPIÃO
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(11, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(11, 'apresentacao', '1. Apresentação', 'presentation', 1, 1, 
'<p>A <strong>${Empresa}</strong> presta serviços de topografia legal para processos de usucapião, fornecendo documentação técnica exigida pelo Poder Judiciário e cartórios de registro de imóveis.</p>
<p>Nosso compromisso é fornecer dados precisos e embasados tecnicamente para subsidiar a ação judicial.</p>',
'["Empresa"]', 1),
(11, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Fornecer a delimitação precisa do imóvel, com coordenadas georreferenciadas, para comprovar a <strong>posse contínua e pacífica</strong> e demonstrar a área ocupada no processo legal de usucapião.</p>',
'["finalidade"]', 1),
(11, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Levantamento para Fins de Usucapião</h4>
<p>Execução de levantamento topográfico da área de <strong>${area_obra}</strong>, compreendendo:</p>
<ul>
<li>Identificação precisa dos limites e confrontantes (norte, sul, leste, oeste);</li>
<li>Relação detalhada de benfeitorias existentes (residências, cercas, benfeitorias);</li>
<li>Tempo de ocupação comprovado através de documentos ou testemunhas;</li>
<li>Memorial descritivo detalhado para embasar ação judicial;</li>
<li>Georreferenciamento da área ao Sistema SIRGAS 2000.</li>
</ul>',
'["area_obra"]', 1),
(11, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Pesquisa Documental</h4>
<p>Análise de documentos existentes (escrituras, matrículas, IPTU) e entrevista com o possuidor para compreender a história da ocupação.</p>

<h4>Etapa 2: Levantamento de Campo</h4>
<p>Utilização de GPS RTK e Estação Total para demarcação precisa dos limites. Identificação e entrevista com confrontantes quando possível.</p>

<h4>Etapa 3: Registro Fotográfico</h4>
<p>Fotos datadas do terreno, acessos, benfeitorias e confrontantes, com coordenadas GPS de cada ponto fotografado.</p>

<h4>Etapa 4: Elaboração da Documentação Legal</h4>
<p>Memorial descritivo técnico-jurídico, planta georreferenciada e ART específica para usucapião.</p>',
'[]', 1),
(11, 'documentacao', '5. Documentação para Processo Judicial', 'legal', 5, 1,
'<ul>
<li><strong>Planta Topográfica Georreferenciada:</strong> Com coordenadas UTM dos vértices e área calculada;</li>
<li><strong>Memorial Descritivo Detalhado:</strong> Com confrontantes, benfeitorias e histórico de ocupação;</li>
<li><strong>Relatório Fotográfico:</strong> Fotos datadas e georreferenciadas do imóvel;</li>
<li><strong>ART Específica:</strong> Registrada no CREA com menção ao processo de usucapião;</li>
<li><strong>Declaração Técnica:</strong> Sobre as características da ocupação (quando solicitado).</li>
</ul>',
'[]', 1),
(11, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>GPS/GNSS RTK:</strong> ${GPS} (georreferenciamento de alta precisão);</li>
<li><strong>Estação Total:</strong> ${Estacao_Total} (demarcação de limites);</li>
<li><strong>Câmera Fotográfica:</strong> Com georreferenciamento integrado;</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["GPS","Estacao_Total","Veiculo"]', 1),
(11, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço será executado em <strong>${prazo_execucao}</strong>, divididos em:</p>
<ul>
<li><strong>Campo:</strong> ${dias_campo} dia(s) para levantamento e fotos;</li>
<li><strong>Escritório:</strong> ${dias_escritorio} dia(s) para memorial e planta.</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(11, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(11, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor","restante_percentual","restante_valor"]', 1),
(11, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(11, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>Agradecemos a confiança em nosso trabalho. Estamos à disposição para esclarecimentos adicionais e para prestar esclarecimentos técnicos em audiência, se necessário.</p>
<p>Reiteramos nosso compromisso com a qualidade e precisão dos dados apresentados.</p>',
'["Empresa"]', 1),
(11, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);

-- ============================================================
-- SERVIÇO 12: PLANIMÉTRICO
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(12, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(12, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> é referência em serviços topográficos de alta precisão, atuando com equipamentos modernos e equipe técnica qualificada.</p>
<p>Apresentamos proposta para execução de levantamento planimétrico, conforme especificações técnicas e normas da ABNT NBR 13.133.</p>',
'["Empresa"]', 1),
(12, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Obter representação gráfica da projeção horizontal do terreno, identificando limites, divisas, construções e elementos visíveis, para fins de cadastro, projetos e regularizações.</p>',
'["finalidade"]', 1),
(12, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Levantamento Planimétrico Cadastral</h4>
<p>Execução de levantamento da área de <strong>${area_obra}</strong>, compreendendo:</p>
<ul>
<li>Demarcação do perímetro e confrontantes do imóvel;</li>
<li>Levantamento de todas as construções, edificações e benfeitorias;</li>
<li>Identificação de elementos físicos (muros, cercas, portões, árvores);</li>
<li>Infraestrutura urbana (postes, caixas de passagem, hidrantes);</li>
<li>Demais elementos necessários ao cadastro.</li>
</ul>',
'["area_obra"]', 1),
(12, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Amarração Geodésica</h4>
<p>Implantação de pontos de apoio com GPS GNSS, vinculados ao Sistema Geodésico Brasileiro (SIRGAS 2000).</p>

<h4>Etapa 2: Levantamento de Detalhe</h4>
<p>Utilização de Estação Total para coleta irradiada de todos os elementos do terreno, garantindo precisão milimétrica.</p>

<h4>Etapa 3: Processamento e Desenho</h4>
<p>Processamento dos dados, cálculos de poligonais e elaboração da planta planimétrica em software CAD.</p>',
'[]', 1),
(12, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Planta Planimétrica em escala adequada (PDF e DWG);</li>
<li>Memorial Descritivo do perímetro do imóvel;</li>
<li>ART (Anotação de Responsabilidade Técnica) registrada no CREA;</li>
<li>Relatório Fotográfico do local (quando aplicável).</li>
</ul>',
'[]', 1),
(12, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total} (precisão angular e linear);</li>
<li><strong>GPS/GNSS:</strong> ${GPS} (amarração geodésica);</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(12, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço será executado em <strong>${prazo_execucao}</strong>:</p>
<ul>
<li><strong>Campo:</strong> ${dias_campo} dia(s) para levantamento;</li>
<li><strong>Escritório:</strong> ${dias_escritorio} dia(s) para processamento.</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(12, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(12, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(12, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(12, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>Agradecemos a oportunidade de apresentar nossa proposta. Temos a certeza de que nossa solução técnica atenderá às suas necessidades.</p>
<p>Permanecemos à disposição para esclarecimentos adicionais.</p>',
'["Empresa"]', 1),
(12, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);

-- ============================================================
-- SERVIÇO 13: PLANIALTIMÉTRICO (Topografia Completa)
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(13, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(13, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> é empresa especializada em soluções de Engenharia e Topografia, atuando com equipamentos de alta tecnologia e equipe técnica qualificada.</p>
<p>Nosso objetivo é fornecer dados precisos e confiáveis para garantir a segurança e qualidade do seu projeto, seguindo rigorosamente as normas técnicas da ABNT NBR 13.133.</p>',
'["Empresa"]', 1),
(13, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Registrar simultaneamente posição horizontal e altitudes, gerando planta com curvas de nível e detalhes altimétricos para fins de projeto, terraplenagem e engenharia.</p>',
'["finalidade"]', 1),
(13, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Levantamento Planialtimétrico Cadastral</h4>
<p>Execução completa de levantamento topográfico da área de <strong>${area_obra}</strong>, compreendendo:</p>
<ul>
<li>Demarcação do perímetro e confrontantes;</li>
<li>Levantamento planimétrico de construções, cercas e elementos físicos;</li>
<li>Coleta de pontos cotados para geração de curvas de nível;</li>
<li>Identificação da infraestrutura e redes existentes;</li>
<li>Cálculo de volumes (quando solicitado).</li>
</ul>',
'["area_obra"]', 1),
(13, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Reconhecimento e Amarração Geodésica</h4>
<p>Implantação da rede de apoio com GPS GNSS de dupla frequência, vinculada ao Sistema Geodésico Brasileiro (SIRGAS 2000).</p>

<h4>Etapa 2: Levantamento Planimétrico</h4>
<p>Utilização de Estação Total para coleta irradiada de todos os elementos horizontais do terreno.</p>

<h4>Etapa 3: Levantamento Altimétrico</h4>
<p>Coleta de pontos cotados em grade regular e em locais de mudança de relevo, para geração das curvas de nível.</p>

<h4>Etapa 4: Processamento e CAD</h4>
<p>Processamento dos dados, interpolação das curvas de nível e elaboração da planta planialtimétrica completa.</p>',
'[]', 1),
(13, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Planta Topográfica Planialtimétrica (PDF e DWG);</li>
<li>Curvas de nível com equidistância conforme especificação;</li>
<li>Memorial Descritivo do perímetro;</li>
<li>ART registrada no CREA;</li>
<li>Relatório Fotográfico (quando aplicável).</li>
</ul>',
'[]', 1),
(13, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total};</li>
<li><strong>GPS/GNSS:</strong> ${GPS} (amarração geodésica);</li>
<li><strong>Nível:</strong> Nível de precisão (controle altimétrico);</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(13, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço será executado em <strong>${prazo_execucao}</strong>:</p>
<ul>
<li><strong>Campo:</strong> ${dias_campo} dia(s) para levantamento completo;</li>
<li><strong>Escritório:</strong> ${dias_escritorio} dia(s) para processamento e desenho.</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(13, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(13, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(13, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(13, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>Agradecemos a oportunidade de apresentar nossa proposta. Temos a certeza de que nossa solução técnica fornecerá a base exata necessária para o desenvolvimento do seu projeto.</p>
<p>Permanecemos à disposição para esclarecimentos adicionais.</p>',
'["Empresa"]', 1),
(13, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);

-- ============================================================
-- SERVIÇO 14: OBRA TERRAPLANAGEM
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(14, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(14, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> possui vasta experiência em serviços topográficos para obras de terraplenagem, fornecendo base geométrica precisa para cálculo e controle de volumes.</p>',
'["Empresa"]', 1),
(14, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Fornecer base geométrica para cálculo e controle de volumes, cortes e aterros, garantindo o nivelamento adequado na execução dos serviços de movimentação de terra.</p>',
'["finalidade"]', 1),
(14, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Topografia para Terraplenagem</h4>
<p>Execução de levantamento da área de <strong>${area_obra}</strong>, compreendendo:</p>
<ul>
<li>Levantamento topográfico planialtimétrico da área de intervenção;</li>
<li>Demarcação de cotas de projeto (locação de níveis);</li>
<li>Cálculo de volumes de corte e aterro;</li>
<li>Controle de compactação e níveis de acabamento;</li>
<li>Planta de situação com cotas de projeto vs. cotas de terreno.</li>
</ul>',
'["area_obra"]', 1),
(14, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Levantamento Topográfico Inicial</h4>
<p>Levantamento planialtimétrico completo do terreno natural para obtenção da superfície original.</p>

<h4>Etapa 2: Cálculo de Volumes</h4>
<p>Geração do Modelo Digital do Terreno (MDT) e cálculo de volumes de corte/aterro com base no projeto.</p>

<h4>Etapa 3: Locação de Cotas</h4>
<p>Demarcação no terreno das cotas de projeto, eixos de plataformas, taludes e áreas de escavação.</p>

<h4>Etapa 4: Controle de Execução</h4>
<p>Acompanhamento da obra com medições de controle de níveis e cálculo de volumes executados.</p>',
'[]', 1),
(14, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Planta Topográfica do terreno natural;</li>
<li>Planta de Projeto com cotas e curvas de nível;</li>
<li>Mapa de Cortes e Aterros (seções transversais);</li>
<li>Relatório de Cálculo de Volumes;</li>
<li>Planta de Controle de Execução (acompanhamento);</li>
<li>ART registrada no CREA.</li>
</ul>',
'[]', 1),
(14, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total} (locação e controle);</li>
<li><strong>GPS/GNSS:</strong> ${GPS} (amarração e locação aproximada);</li>
<li><strong>Nível de Precisão:</strong> Controle altimétrico rigoroso;</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(14, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço será executado conforme cronograma da obra:</p>
<ul>
<li><strong>Levantamento inicial:</strong> ${dias_campo} dia(s);</li>
<li><strong>Locação e acompanhamento:</strong> Conforme necessidade da obra;</li>
<li><strong>Entrega de documentos:</strong> ${dias_escritorio} dia(s) após coleta.</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(14, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(14, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(14, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(14, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>Estamos prontos para integrar nossa equipe ao cronograma de sua obra, garantindo precisão nos dados e agilidade na entrega.</p>',
'["Empresa"]', 1),
(14, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);

-- ============================================================
-- SERVIÇO 15: OBRA INDUSTRIAL
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(15, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(15, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> possui expertise em serviços topográficos para obras industriais, garantindo precisão na implantação e monitoramento de estruturas, máquinas e tubulações.</p>',
'["Empresa"]', 1),
(15, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Apoiar a implantação e monitoramento de estruturas industriais, garantindo precisão na locação de fundações, eixos, máquinas, tubulações e elementos construtivos.</p>',
'["finalidade"]', 1),
(15, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Topografia para Obra Industrial</h4>
<p>Execução de serviços na área de <strong>${area_obra}</strong>:</p>
<ul>
<li>Levantamento topográfico do terreno industrial;</li>
<li>Locação de eixos de fundações, colunas e estruturas metálicas;</li>
<li>Nivelamento de bases de máquinas e equipamentos;</li>
<li>Controle de verticalidade de estruturas;</li>
<li>Locação de redes de utilidades (tubulações, eletrodutos);</li>
<li>Monitoramento de assentamentos e recalques.</li>
</ul>',
'["area_obra"]', 1),
(15, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Levantamento de Base</h4>
<p>Levantamento planialtimétrico de alta precisão do terreno industrial.</p>

<h4>Etapa 2: Locação de Fundações</h4>
<p>Transferência dos eixos do projeto para o terreno com precisão milimétrica, utilizando Estação Total e GPS RTK.</p>

<h4>Etapa 3: Controle de Estruturas</h4>
<p>Monitoramento da verticalidade de pilares e estruturas metálicas durante a montagem.</p>

<h4>Etapa 4: Nivelamento Industrial</h4>
<p>Nivelamento de bases de máquinas com tolerâncias rigorosas, utilizando nível de precisão.</p>',
'[]', 1),
(15, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Planta Topográfica da área industrial;</li>
<li>Relatório de Locação de Eixos e Fundações;</li>
<li>Laudo de Nivelamento de Bases;</li>
<li>Relatório de Controle de Verticalidade;</li>
<li>Planta de Redes de Utilidades (quando aplicável);</li>
<li>ART registrada no CREA.</li>
</ul>',
'[]', 1),
(15, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total de Alta Precisão:</strong> ${Estacao_Total};</li>
<li><strong>GPS/GNSS RTK:</strong> ${GPS} (locação primária);</li>
<li><strong>Nível de Precisão Digital:</strong> Nivelamento rigoroso;</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(15, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço será executado conforme cronograma da obra industrial:</p>
<ul>
<li><strong>Levantamento inicial:</strong> ${dias_campo} dia(s);</li>
<li><strong>Locações e controles:</strong> Conforme demanda da obra;</li>
<li><strong>Documentação:</strong> ${dias_escritorio} dia(s) após medições.</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(15, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(15, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(15, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(15, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>Entendemos a criticidade da precisão em obras industriais. Nossa equipe está preparada para atender às rigorosas tolerâncias exigidas.</p>',
'["Empresa"]', 1),
(15, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);
