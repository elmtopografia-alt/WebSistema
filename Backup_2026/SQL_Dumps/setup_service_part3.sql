-- ARQUITETURA EXPLÍCITA DE PROPOSTAS - PART 3
-- ============================================================
-- SERVIÇO 20: DESDOBRAMENTO
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(20, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(20, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> executa serviços de desdobramento de terrenos com precisão técnica e conformidade legal para aprovação em cartórios e órgãos municipais.</p>',
'["Empresa"]', 1),
(20, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Delimitar e fracionar áreas de um terreno em lotes menores, definindo novas divisas, áreas individuais e coordenadas, para regularização e aprovação junto aos órgãos competentes.</p>',
'["finalidade"]', 1),
(20, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Desdobramento de Terreno</h4>
<p>Execução de serviços na área total de <strong>${area_obra}</strong>:</p>
<ul>
<li>Levantamento planialtimétrico completo da área matriz;</li>
<li>Elaboração de projeto de loteamento (divisão em lotes);</li>
<li>Cálculo de áreas individuais de cada lote;</li>
<li><strong>Locação das novas divisas no terreno</strong> (marcação física);</li>
<li>Memoriais descritivos individuais por lote;</li>
<li>Documentação para registro em cartório.</li>
</ul>',
'["area_obra"]', 1),
(20, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Levantamento da Área Matriz</h4>
<p>Execução de levantamento planialtimétrico completo do terreno original, identificando limites atuais e confrontantes.</p>

<h4>Etapa 2: Projeto de Loteamento (Escritório)</h4>
<p>Elaboração do projeto de divisão em lotes menores, respeitando:
<ul>
<li>Zoneamento municipal (uso e ocupação do solo);</li>
<li>Áreas de preservação permanente (APPs);</li>
<li>Largura mínima de lotes e testadas;</li>
<li>Áreas de doação para via pública (quando exigido).</li>
</ul></p>

<h4>Etapa 3: Aprovação Preliminar</h4>
<p>Apresentação do projeto ao cliente e, se necessário, à prefeitura para aprovação prévia.</p>

<h4>Etapa 4: Locação das Novas Divisas (Campo)</h4>
<p><strong>Esta é a etapa diferencial:</strong> Retornamos ao campo para <strong>marcar fisicamente no terreno</strong> as novas divisas dos lotes com estacas de madeira pintadas ou marcos de concreto, permitindo que o cliente visualize o resultado final antes da documentação definitiva.</p>

<h4>Etapa 5: Documentação para Registro</h4>
<p>Elaboração dos memoriais descritivos individuais, plantas de situação do loteamento e processo de desdobro para cartório/prefeitura.</p>',
'[]', 1),
(20, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Planta da Área Matriz (antes da divisão);</li>
<li>Planta de Loteamento (após divisão);</li>
<li>Memoriais Descritivos individuais (um por lote);</li>
<li>Relatório de Locação das Novas Divisas;</li>
<li>ART registrada no CREA;</li>
<li>Documentação para registro cartorial.</li>
</ul>',
'[]', 1),
(20, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total} (locação precisa das divisas);</li>
<li><strong>GPS/GNSS:</strong> ${GPS} (amarração geodésica);</li>
<li><strong>Material de Demarcação:</strong> Estacas, tinta, marcos;</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(20, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço de desdobramento exige etapas sequenciais:</p>
<ul>
<li><strong>Levantamento:</strong> ${dias_campo} dia(s);</li>
<li><strong>Projeto e aprovação:</strong> 5 a 10 dias;</li>
<li><strong>Locação das divisas:</strong> 1 a 2 dias;</li>
<li><strong>Documentação final:</strong> ${dias_escritorio} dia(s).</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(20, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(20, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(20, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(20, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>O desdobramento é um processo que envolve aspectos técnicos e legais. Nossa equipe está preparada para orientar o cliente em todas as etapas, desde o projeto até o registro cartorial.</p>',
'["Empresa"]', 1),
(20, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);

-- ============================================================
-- SERVIÇO 21: CONFERÊNCIA
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(21, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(21, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> executa serviços de conferência topográfica para verificação e validação de medidas existentes, identificando divergências entre projeto e realidade do terreno.</p>',
'["Empresa"]', 1),
(21, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Verificar e validar medidas existentes no local, confrontando com documentos e projetos, para confirmar área, dimensões, limites e possíveis divergências.</p>',
'["finalidade"]', 1),
(21, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Conferência Topográfica</h4>
<p>Serviço na área de <strong>${area_obra}</strong>:</p>
<ul>
<li>Levantamento atual do terreno;</li>
<li>Confrontação com documentação existente (escrituras, plantas anteriores);</li>
<li>Identificação de divergências de área, limites ou confrontantes;</li>
<li>Relatório técnico de conformidade ou não-conformidade;</li>
<li>Medições de controle de obras em execução.</li>
</ul>',
'["area_obra"]', 1),
(21, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Análise Documental</h4>
<p>Estudo da documentação existente: escrituras, plantas anteriores, projetos, matrículas.</p>

<h4>Etapa 2: Levantamento de Conferência</h4>
<p>Levantamento topográfico atual do terreno com a mesma precisão de um levantamento cadastral.</p>

<h4>Etapa 3: Confrontação</h4>
<p>Sobreposição dos dados levantados com a documentação, identificando e quantificando divergências.</p>

<h4>Etapa 4: Laudo Técnico</h4>
<p>Elaboração de laudo detalhado com as divergências encontradas, quando houver.</p>',
'[]', 1),
(21, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Planta de Conferência (situação atual);</li>
<li>Laudo Técnico de Conferência;</li>
<li>Relatório de Divergências (se houver);</li>
<li>ART registrada no CREA.</li>
</ul>',
'[]', 1),
(21, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total};</li>
<li><strong>GPS/GNSS:</strong> ${GPS};</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(21, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço é ágil:</p>
<ul>
<li><strong>Campo:</strong> ${dias_campo} dia(s);</li>
<li><strong>Análise e laudo:</strong> ${dias_escritorio} dia(s).</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(21, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(21, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor"]', 1),
(21, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(21, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>A conferência topográfica é essencial para evitar problemas futuros com áreas, limites ou regularização. Garantimos imparcialidade e precisão técnica.</p>',
'["Empresa"]', 1),
(21, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);

-- ============================================================
-- SERVIÇO 22: AVULSO
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(22, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(22, 'apresentacao', '1. Apresentação', 'presentation', 1, 1, '<p>A <strong>${Empresa}</strong> executa serviços topográficos avulsos conforme demanda específica do cliente.</p>', '["Empresa"]', 1),
(22, 'finalidade', '2. Finalidade', 'presentation', 2, 1, '<p>Realização de serviços topográficos avulsos para atender demandas variáveis do contratante, incluindo medições, conferências e demais atividades necessárias conforme necessidade diária.</p>', '[]', 1),
(22, 'escopo', '3. Escopo', 'technical', 3, 1, '<h4>Serviços Avulsos de Topografia</h4><p>Área: ${area_obra}</p><p>Serviços a serem executados conforme solicitação:</p><ul><li>Medições pontuais;</li><li>Demarcações simples;</li><li>Nivelamentos;</li><li>Outros serviços de pequeno porte.</li></ul>', '["area_obra"]', 1),
(22, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Definição do Serviço</h4>
<p>Reunião com o cliente para entender a necessidade específica e definir o escopo exato do trabalho.</p>

<h4>Etapa 2: Execução Técnica</h4>
<p>Utilização de equipamentos adequados (Estação Total, GPS ou nível) conforme a natureza do serviço solicitado.</p>

<h4>Etapa 3: Entrega dos Resultados</h4>
<p>Entrega de croquis, relatórios ou dados brutos conforme solicitado pelo cliente.</p>',
'[]', 1),
(22, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li>Relatório simplificado do serviço executado;</li>
<li>Croquis de campo quando aplicável;</li>
<li>Planilha de dados medidos;</li>
<li>ART (Anotação de Responsabilidade Técnica) quando exigida.</li>
</ul>',
'[]', 1),
(22, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total} (medições angulares e lineares);</li>
<li><strong>GPS/GNSS:</strong> ${GPS} (coordenadas geográficas quando necessário);</li>
<li><strong>Nível:</strong> Nível óptico ou digital (nivelamentos);</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(22, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O prazo é variável conforme a complexidade do serviço:</p>
<ul>
<li><strong>Serviços simples:</strong> Até ${dias_campo} dia(s) em campo;</li>
<li><strong>Processamento:</strong> ${dias_escritorio} dia(s) quando necessário;</li>
<li><strong>Prazo total estimado:</strong> ${prazo_execucao}.</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(22, 'investimento', '8. Investimento', 'financial', 8, 1, '<p>${ValorProposta} (${ValorExtenso})</p>', '["ValorProposta","ValorExtenso"]', 1),
(22, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor","restante_percentual","restante_valor"]', 1),
(22, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(22, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>Serviços avulsos são cobrados conforme complexidade e tempo dedicado. Entre em contato para discutirmos sua necessidade específica.</p>',
'["Empresa"]', 1),
(22, 'rodape', 'Rodapé', 'layout', 99, 1, '${Empresa}', '["Empresa","CNPJ"]', 1);

-- ============================================================
-- SERVIÇO 23: RETIFICAÇÃO DE ÁREA (COMPLETO)
-- ============================================================
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(23, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(23, 'apresentacao', '1. Apresentação', 'presentation', 1, 1,
'<p>A <strong>${Empresa}</strong> executa serviços de retificação de área com precisão técnica, garantindo conformidade entre a documentação cartorial e a realidade geométrica do terreno.</p>',
'["Empresa"]', 1),
(23, 'finalidade', '2. Finalidade', 'presentation', 2, 1,
'<p>Regularizar a área do imóvel junto a Cartórios de Registro de Imóveis e Prefeituras, corrigindo divergências entre a documentação existente e a realidade do terreno ocupado.</p>',
'["finalidade"]', 1),
(23, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1,
'<h4>Retificação de Área</h4>
<p>Execução de serviços na área de <strong>${area_obra}</strong>:</p>
<ul>
<li>Levantamento planialtimétrico completo do terreno;</li>
<li>Análise documental detalhada (escritura, matrícula, certidão de origem);</li>
<li>Identificação e quantificação de divergências;</li>
<li>Elaboração de nova documentação técnica corrigida;</li>
<li>Acompanhamento do processo junto ao cartório de registro.</li>
</ul>',
'["area_obra"]', 1),
(23, 'metodologia', '4. Metodologia', 'technical', 4, 1,
'<h4>Etapa 1: Análise Documental</h4>
<p>Estudo detalhado da escritura pública, matrícula atual no RGI, certidão de origem da Prefeitura e documentos históricos.</p>

<h4>Etapa 2: Levantamento de Campo</h4>
<p>Medição precisa do perímetro real do terreno ocupado, identificando confrontantes e benfeitorias.</p>

<h4>Etapa 3: Confrontação e Análise</h4>
<p>Comparação entre dados documentais e dados de campo, identificando diferenças de área, deslocamento de limites ou divergências de confrontantes.</p>

<h4>Etapa 4: Elaboração da Retificação</h4>
<p>Preparação da nova documentação técnica com o memorial descritivo retificado e planta georreferenciada.</p>

<h4>Etapa 5: Acompanhamento Cartorial</h4>
<p>Apoio no processo de retificação junto ao cartório, incluindo esclarecimentos técnicos quando solicitados.</p>',
'[]', 1),
(23, 'documentacao', '5. Documentação Gerada', 'technical', 5, 1,
'<ul>
<li><strong>Planta Topográfica Georreferenciada:</strong> Nova planta com limites retificados;</li>
<li><strong>Memorial Descritivo Retificado:</strong> Confrontantes e medidas corrigidas;</li>
<li><strong>Laudo de Confrontação:</strong> Análise técnica das divergências encontradas;</li>
<li><strong>Relatório Fotográfico:</strong> Documentação visual do terreno e confrontantes;</li>
<li><strong>ART Específica:</strong> Registro no CREA para retificação de área;</li>
<li><strong>Documentação para Cartório:</strong> Processo de retificação de registro.</li>
</ul>',
'[]', 1),
(23, 'equipamentos', '6. Equipamentos', 'technical', 6, 1,
'<ul>
<li><strong>Estação Total:</strong> ${Estacao_Total} (medições precisas do perímetro);</li>
<li><strong>GPS/GNSS RTK:</strong> ${GPS} (georreferenciamento dos vértices);</li>
<li><strong>Veículo:</strong> ${Veiculo}.</li>
</ul>',
'["Estacao_Total","GPS","Veiculo"]', 1),
(23, 'cronograma', '7. Prazo de Execução', 'technical', 7, 1,
'<p>O serviço de retificação envolve etapas técnicas e cartoriais:</p>
<ul>
<li><strong>Levantamento de campo:</strong> ${dias_campo} dia(s);</li>
<li><strong>Análise documental e elaboração:</strong> 3 a 5 dias;</li>
<li><strong>Documentação final:</strong> ${dias_escritorio} dia(s);</li>
<li><strong>Prazo total estimado:</strong> ${prazo_execucao} (sem considerar tramitação cartorial).</li>
</ul>',
'["prazo_execucao","dias_campo","dias_escritorio"]', 1),
(23, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta","ValorExtenso"]', 1),
(23, 'condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual","mobilizacao_valor","restante_percentual","restante_valor"]', 1),
(23, 'dados_bancarios', '10. Dados Bancários', 'financial', 10, 1, NULL, '["Banco","Agencia","Conta","PIX"]', 1),
(23, 'consideracoes', '11. Considerações Finais', 'legal', 11, 1,
'<p>A retificação de área é um processo técnico-jurídico que requer precisão e conhecimento cartorial. Nossa equipe está preparada para orientar o cliente em todas as etapas, desde o levantamento até a conclusão do registro.</p>
<p>Ressaltamos que o prazo total do processo depende também da tramitação no cartório de registro de imóveis.</p>',
'["Empresa"]', 1),
(23, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa","CNPJ","whatsapp"]', 1);
