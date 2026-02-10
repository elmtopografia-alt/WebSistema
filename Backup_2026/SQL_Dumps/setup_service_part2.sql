-- ARQUITETURA EXPLÍCITA DE PROPOSTAS - PART 2
-- ============================================================
-- SERVIÇO 16: OBRA CIVIL
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(16, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(16, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> oferece serviços topográficos completos para obras civis, desde a implantação até o acompanhamento da execução.</p>',
'["Empresa"]', 1),
(16, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Orientar a execução de edificações e infraestrutura civil, fornecendo eixos, alinhamentos e cotas para que a obra siga exatamente o projeto aprovado.</p>',
'["finalidade"]', 1),
(16, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Topografia para Obra Civil</h4>
<p>Execução de serviços na área de <strong>${area_obra}</strong>:</p>
<ul>
<li>Levantamento topográfico do terreno;</li>
<li>Locação de gabarito e eixos de construção;</li>
<li>Nivelamento de lajes e pisos;</li>
<li>Controle de altura de pilares e paredes;</li>
<li>Locação de infraestrutura (esgotos, águas pluviais);</li>
<li>Planta de situação final (as built).</li>
</ul>',
'["area_obra"]', 1),
(16, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Levantamento de Base</h4>
<p>Levantamento planialtimétrico completo do terreno da obra.</p>

<h4>Etapa 2: Locação do Gabarito</h4>
<p>Transferência dos eixos do projeto arquitetônico para o terreno, demarcando o alinhamento das construções.</p>

<h4>Etapa 3: Controle de Níveis</h4>
<p>Demarcação de cotas de nível para fundações, lajes, pisos e outros elementos verticais.</p>

<h4>Etapa 4: Acompanhamento da Obra</h4>
<p>Controles periódicos durante a execução para garantir conformidade com o projeto.</p>',
'[]', 1),
(16, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Planta Topográfica do terreno;</li>
<li>Relatório de Locação de Gabarito;</li>
<li>Laudo de Nivelamento;</li>
<li>Planta de Situação Final (As Built);</li>
<li>ART registrada no CREA.</li>
</ul>',
'[]', 1),
(16, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total};</li>
<li><strong>GPS/GNSS:</strong> ${GPS};</li>
<li><strong>Nível:</strong> Nível óptico ou digital;</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(16, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço acompanha o cronograma da obra:</p>
<ul>
<li><strong>Levantamento inicial:</strong> ${dias_campo} dia(s);</li>
<li><strong>Locações e controles:</strong> Conforme necessidade;</li>
<li><strong>Documentação final:</strong> ${dias_escritorio} dia(s).</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(16, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(16, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(16, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(16, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>Estamos preparados para integrar nossos serviços ao cronograma de sua obra civil, garantindo precisão e agilidade.</p>',
'["Empresa"]', 1),
(16, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);

-- ============================================================
-- SERVIÇO 17: LOCAÇÃO DE OBRA
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(17, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(17, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> executa serviços de locação topográfica com precisão milimétrica, transferindo do papel para o terreno as coordenadas do projeto estrutural.</p>',
'["Empresa"]', 1),
(17, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Realizar a marcação precisa, no gabarito (tabeira), dos cruzamentos que definem os centros de tubulões, estacas, sapatas e demais elementos da fundação.</p>',
'["finalidade"]', 1),
(17, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Locação de Obra (Gabarito)</h4>
<p>Serviço de locação na área de <strong>${area_obra}</strong>:</p>
<ul>
<li>Reconhecimento do terreno e amarração ao projeto;</li>
<li>Demarcação dos eixos principais e secundários;</li>
<li>Marcação dos centros de fundações (tubulões, estacas, sapatas);</li>
<li>Nivelamento de cotas de fundação;</li>
<li>Entrega de memorial de locação assinado.</li>
</ul>',
'["area_obra"]', 1),
(17, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Estudo do Projeto</h4>
<p>Análise das plantas estruturais para identificação dos eixos e coordenadas de cada elemento.</p>

<h4>Etapa 2: Amarração no Terreno</h4>
<p>Estabelecimento de base topográfica no terreno, vinculada ao sistema de coordenadas do projeto.</p>

<h4>Etapa 3: Demarcação dos Eixos</h4>
<p>Utilização de Estação Total para demarcar no gabarito (tabeira de madeira) os cruzamentos dos eixos das fundações.</p>

<h4>Etapa 4: Verificação</h4>
<p>Conferência das diagonais e cotas para garantir precisão antes da liberação para a equipe de obra.</p>',
'[]', 1),
(17, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Memorial de Locação de Obra;</li>
<li>Croqui de locação com coordenadas;</li>
<li>Relatório fotográfico da demarcação;</li>
<li>ART registrada no CREA.</li>
</ul>',
'[]', 1),
(17, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total de Precisão:</strong> ${Estacao_Total} (precisão angular de 1\" ou melhor);</li>
<li><strong>GPS/GNSS RTK:</strong> ${GPS} (amarração inicial);</li>
<li><strong>Gabarito/Tabeira:</strong> Estrutura de madeira para marcação;</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(17, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço de locação é rápido e preciso:</p>
<ul>
<li><strong>Preparação:</strong> 1 dia;</li>
<li><strong>Locação no terreno:</strong> ${dias_campo} dia(s);</li>
<li><strong>Documentação:</strong> ${dias_escritorio} dia(s).</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(17, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(17, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(17, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(17, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>A precisão na locação é fundamental para o sucesso da obra. Garantimos tolerâncias compatíveis com as exigências do projeto estrutural.</p>',
'["Empresa"]', 1),
(17, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);

-- ============================================================
-- SERVIÇO 18: LOCAÇÃO TERRAPLENAGEM
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(18, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(18, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> executa locação específica para obras de terraplenagem, garantindo a correta execução de cortes, aterros e plataformas.</p>',
'["Empresa"]', 1),
(18, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Marcar no terreno os limites, taludes, níveis e seções especificadas para cortes e aterros, garantindo que a terraplenagem seja executada conforme projeto e volumes estimados.</p>',
'["finalidade"]', 1),
(18, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Locação para Terraplenagem</h4>
<p>Serviço na área de <strong>${area_obra}</strong>:</p>
<ul>
<li>Demarcação dos limites da área de intervenção;</li>
<li>Marcação das cotas de projeto (níveis de plataforma);</li>
<li>Locação de taludes (corte e aterro);</li>
<li>Demarcação de seções transversais para controle;</li>
<li>Acompanhamento da execução com medições de controle.</li>
</ul>',
'["area_obra"]', 1),
(18, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Análise do Projeto Geométrico</h4>
<p>Estudo das curvas de nível de projeto, seções transversais e volumes de movimentação de terra.</p>

<h4>Etapa 2: Demarcação dos Limites</h4>
<p>Marcação no terreno dos limites da área de corte e aterro, utilizando estacas e pintura.</p>

<h4>Etapa 3: Locação de Cotas</h4>
<p>Demarcação das cotas de projeto a intervalos regulares, utilizando nível de precisão ou Estação Total.</p>

<h4>Etapa 4: Controle de Execução</h4>
<p>Medições periódicas para verificar se o nível de corte/aterro está conforme o projeto.</p>',
'[]', 1),
(18, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Croqui de locação com cotas de projeto;</li>
<li>Relatório de controle de execução;</li>
<li>Seções transversais de controle;</li>
<li>ART registrada no CREA.</li>
</ul>',
'[]', 1),
(18, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total};</li>
<li><strong>Nível de Precisão:</strong> Controle altimétrico;</li>
<li><strong>GPS/GNSS:</strong> ${GPS} (limites gerais);</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(18, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço acompanha a execução da terraplenagem:</p>
<ul>
<li><strong>Locação inicial:</strong> ${dias_campo} dia(s);</li>
<li><strong>Controles:</strong> Conforme avanço da obra;</li>
<li><strong>Documentação:</strong> ${dias_escritorio} dia(s).</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(18, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(18, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(18, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(18, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>A locação precisa é essencial para o controle de volumes e o sucesso da terraplenagem.</p>',
'["Empresa"]', 1),
(18, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);

-- ============================================================
-- SERVIÇO 19: DRONE (AEROFOTOGRAMETRIA)
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(19, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(19, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> apresenta esta proposta técnica para execução de <strong>Levantamento Planialtimétrico via Aerofotogrametria com Drones (VANTs)</strong>.</p>
<p>Trata-se de <strong>Engenharia de Precisão</strong>, não simples filmagem aérea. Geramos representação digital fiel do terreno com coordenadas exatas (Latitude, Longitude, Altitude) para base legal de projetos de arquitetura, loteamentos, regularização e cálculo de volumes.</p>',
'["Empresa"]', 1),
(19, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Obter mapeamento aéreo detalhado, ortomosaicos georreferenciados, Modelo Digital de Terreno (MDT), nuvens de pontos densas e ortomosaicos para análise, planejamento, acompanhamento de obras e levantamento de grandes áreas com rapidez e precisão.</p>',
'["finalidade"]', 1),
(19, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Levantamento Fotogramétrico com Drone</h4>
<p>Execução de levantamento da área de <strong>${area_obra}</strong>, compreendendo:</p>
<ul>
<li>Planejamento de voo e estudo de viabilidade aérea;</li>
<li>Implantação de Pontos de Controle Terrestre (GCPs) com GPS RTK;</li>
<li>Captura de imagens aéreas com sobreposição adequada (80% longitudinal, 70% lateral);</li>
<li>Processamento fotogramétrico e geração de ortomosaico georreferenciado;</li>
<li>Geração de MDT (Modelo Digital de Terreno) e MDS (Modelo Digital de Superfície);</li>
<li>Extração de curvas de nível e elementos vetoriais;</li>
<li>Cálculo de volumes (quando solicitado).</li>
</ul>',
'["area_obra"]', 1),
(19, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Planejamento e Configuração de Voo (Escritório)</h4>
<p>Estudo da área via imagens de satélite, definição da altura de voo para garantir a resolução desejada (GSD), cálculo da grade de cobertura e verificação de restrições aéreas (DECEA).</p>

<h4>Etapa 2: Apoio Terrestre - Pontos de Controle (Campo)</h4>
<p>Distribuição de alvos (GCPs - Ground Control Points) no terreno e coleta de suas coordenadas exatas com GPS Geodésico de Alta Precisão (RTK). Estes pontos servem como "âncoras" garantindo precisão centimétrica no ortomosaico final.</p>

<h4>Etapa 3: Execução do Voo (Campo)</h4>
<p>Checklist de segurança: verificação de baterias, hélices, condições meteorológicas (vento < 30 km/h, sem precipitação). O drone executa rota autônoma programada, capturando centenas de fotos em ângulos nadir (verticais) e oblíquos.</p>

<h4>Etapa 4: Processamento Fotogramétrico (Escritório)</h4>
<p>Utilização de Workstations de alta performance e software especializado: (1) Alinhamento das fotos e criação da nuvem de pontos esparsa, (2) Geração da Nuvem de Pontos Densa com milhões de pontos 3D, (3) Georreferenciamento com os GCPs para precisão absoluta, (4) Geração do Ortomosaico e dos Modelos Digitais.</p>

<h4>Etapa 5: Vetorização e Desenho Técnico (CAD)</h4>
<p>Desenhista técnico utiliza o modelo 3D e o ortomosaico para vetorizar elementos do terreno: guias, cercas, edificações, postes, vegetação, e gerar as Curvas de Nível no software CAD.</p>',
'[]', 1),
(19, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li><strong>Ortomosaico Georreferenciado (TIF/JPG + TFW/JGW):</strong> "Foto" gigante da área em escala real, corrigida geometricamente, com precisão centimétrica;</li>
<li><strong>MDT (Modelo Digital de Terreno):</strong> Representação 3D do solo nu (sem vegetação e construções) em formato compatível com softwares de terraplenagem;</li>
<li><strong>MDS (Modelo Digital de Superfície):</strong> Representação 3D da superfície com todos os elementos;</li>
<li><strong>Nuvem de Pontos Densa (LAS/XYZ):</strong> Arquivo com milhões de pontos 3D colorizados;</li>
<li><strong>Curvas de Nível (DWG/DXF):</strong> Arquivo CAD com equidistância conforme especificação técnica;</li>
<li><strong>Planta Topográfica Planialtimétrica (PDF):</strong> Mapa finalizado com legenda, escala gráfica, norte, coordenadas e memorial;</li>
<li><strong>Relatório de Processamento:</strong> Estatísticas de precisão alcançada (erro médio quadrado nos GCPs);</li>
<li><strong>ART (Anotação de Responsabilidade Técnica):</strong> Registro no CREA do responsável técnico.</li>
</ul>',
'[]', 1),
(19, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Drone VANT:</strong> ${Drone} (câmera de alta resolução, sistema RTK para precisão de posição do drone);</li>
<li><strong>GPS de Apoio (RTK):</strong> ${GPS} (coleta de GCPs com precisão centimétrica horizontal e vertical);</li>
<li><strong>Estação Total:</strong> ${Estacao_Total} (verificação de pontos de controle e coleta de detalhes);</li>
<li><strong>Software Fotogramétrico:</strong> Licenciado para processamento de imagens;</li>
<li><strong>Workstation:</strong> Computador de alta performance para processamento;</li>
<li><strong>Veículo:</strong> ${Veiculo} (transporte da equipe e equipamentos).</li>
</ul>',
'["Drone","GPS","Estacao_Total","Veiculo"]', 1),
(19, 'cronograma_drone', '7. Cronograma de Execução', 'technical', 7, 1,
'<p class="section-intro">O cumprimento dos prazos depende de condições climáticas favoráveis (ausência de chuva e ventos superiores a 30 km/h).</p>
<table class="proposal-table">
<thead><tr><th>Etapa</th><th>Descrição</th><th>Prazo Estimado</th></tr></thead>
<tbody>
<tr><td><strong>1. Mobilização</strong></td><td>Planejamento, análise DECEA e ida a campo</td><td>Até 02 dias</td></tr>
<tr><td><strong>2. Campo (GCPs)</strong></td><td>Instalação de pontos de controle terrestre</td><td>01 dia</td></tr>
<tr><td><strong>3. Campo (Voo)</strong></td><td>Execução do voo de mapeamento</td><td>01 dia</td></tr>
<tr><td><strong>4. Processamento</strong></td><td>Geração da nuvem de pontos e ortomosaico</td><td>03 a 05 dias</td></tr>
<tr><td><strong>5. CAD/Vetorização</strong></td><td>Desenho técnico e curvas de nível</td><td>03 a 05 dias</td></tr>
<tr class="total-row"><td colspan="2"><strong>TOTAL ESTIMADO</strong></td><td><strong>07 a 12 dias úteis</strong></td></tr>
</tbody>
</table>',
'[]', 1),
(19, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(19, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(19, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(19, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>Agradecemos a oportunidade de apresentar nossa proposta técnica. A Aerofotogrametria com Drones representa o estado da arte em levantamento topográfico, combinando rapidez, segurança e precisão.</p>
<p>Ressaltamos que a qualidade do produto final depende da colaboração do cliente no acesso à área e na liberação de voos. Permanecemos à disposição para esclarecimentos técnicos adicionais.</p>',
'["Empresa"]', 1),
(19, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);
