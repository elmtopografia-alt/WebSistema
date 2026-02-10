-- ARQUITETURA EXPLÍCITA DE PROPOSTAS - PART 4
-- SERVIÇOS FALTANTES (LEGADO + NOVOS)
-- ============================================================

-- Template Padrão para serviços sem conteúdo específico definido
-- IDs: 1 a 10, 24, 25

-- SERVIÇO 1: LEVANTAMENTO PLANIMÉTRICO (LEGADO)
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(1, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(1, 'apresentacao', '1. Apresentação', 'presentation', 1, 1, '<p>A <strong>${Empresa}</strong> apresenta proposta técnica e comercial para prestação de serviços de topografia.</p>', '["Empresa"]', 1),
(1, 'escopo', '3. Escopo do Serviço', 'technical', 3, 1, '<h4>Escopo do Serviço</h4><p>Execução de levantamento topográfico na área de <strong>${area_obra}</strong>.</p>', '["area_obra"]', 1),
(1, 'metodologia', '4. Metodologia', 'technical', 4, 1, '<h4>Metodologia</h4><p>Utilização de equipamentos de precisão (Estação Total e GPS) conforme normas da ABNT.</p>', '[]', 1),
(1, 'equipamentos', '6. Equipamentos', 'technical', 6, 1, '<ul><li>Estação Total: ${Estacao_Total}</li><li>GPS: ${GPS}</li><li>Veículo: ${Veiculo}</li></ul>', '["Estacao_Total","GPS","Veiculo"]', 1),
(1, 'cronograma', '7. Prazo', 'technical', 7, 1, '<p>Prazo estimado: ${prazo_execucao}</p>', '["prazo_execucao"]', 1),
(1, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta"]', 1),
(1, 'condicoes_pagamento', '9. Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual"]', 1),
(1, 'dados_bancarios', '10. Banco', 'financial', 10, 1, NULL, '["Banco"]', 1),
(1, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa"]', 1);

-- SERVIÇO 2: LEVANTAMENTO PLANIALTIMÉTRICO (LEGADO)
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(2, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(2, 'apresentacao', '1. Apresentação', 'presentation', 1, 1, '<p>A <strong>${Empresa}</strong> apresenta proposta para levantamento planialtimétrico.</p>', '["Empresa"]', 1),
(2, 'escopo', '3. Escopo', 'technical', 3, 1, '<p>Levantamento planialtimétrico cadastral de <strong>${area_obra}</strong>.</p>', '["area_obra"]', 1),
(2, 'metodologia', '4. Metodologia', 'technical', 4, 1, '<p>Medição de coordenadas X, Y e Z para geração de curvas de nível.</p>', '[]', 1),
(2, 'equipamentos', '6. Equipamentos', 'technical', 6, 1, '<ul><li>Estação Total: ${Estacao_Total}</li><li>GPS: ${GPS}</li></ul>', '["Estacao_Total","GPS"]', 1),
(2, 'cronograma', '7. Prazo', 'technical', 7, 1, '<p>Prazo: ${prazo_execucao}</p>', '["prazo_execucao"]', 1),
(2, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta"]', 1),
(2, 'condicoes_pagamento', '9. Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual"]', 1),
(2, 'dados_bancarios', '10. Banco', 'financial', 10, 1, NULL, '["Banco"]', 1),
(2, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa"]', 1);

-- SERVIÇO 3: GEORREFERENCIAMENTO (LEGADO)
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) VALUES
(3, 'cabecalho', 'Cabeçalho', 'layout', 0, 1, NULL, '["Cidade","DataExtenso","numero_proposta"]', 1),
(3, 'apresentacao', '1. Apresentação', 'presentation', 1, 1, '<p>Proposta para Georreferenciamento de Imóvel Rural.</p>', '["Empresa"]', 1),
(3, 'escopo', '3. Escopo', 'technical', 3, 1, '<p>Georreferenciamento conforme norma do INCRA (Lei 10.267).</p>', '[]', 1),
(3, 'metodologia', '4. Metodologia', 'technical', 4, 1, '<p>Rastreio com GPS Geodésico de Dupla Frequência e certificação no SIGEF.</p>', '[]', 1),
(3, 'equipamentos', '6. Equipamentos', 'technical', 6, 1, '<ul><li>GPS RTK: ${GPS}</li></ul>', '["GPS"]', 1),
(3, 'cronograma', '7. Prazo', 'technical', 7, 1, '<p>Prazo: ${prazo_execucao}</p>', '["prazo_execucao"]', 1),
(3, 'investimento', '8. Investimento', 'financial', 8, 1, NULL, '["ValorProposta"]', 1),
(3, 'condicoes_pagamento', '9. Pagamento', 'financial', 9, 1, NULL, '["mobilizacao_percentual"]', 1),
(3, 'dados_bancarios', '10. Banco', 'financial', 10, 1, NULL, '["Banco"]', 1),
(3, 'rodape', 'Rodapé', 'layout', 99, 1, NULL, '["Empresa"]', 1);

-- REPETE PADRÃO PARA OS DEMAIS ID 4 a 10, 24, 25 PARA EVITAR ERROS DE "MISSING"
-- SERVIÇO 4: LOCAÇÃO DE OBRA
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) SELECT 4, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active FROM service_type_blocks WHERE service_type_id = 1;
-- SERVIÇO 5: BATIMETRIA
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) SELECT 5, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active FROM service_type_blocks WHERE service_type_id = 1;
-- SERVIÇO 6: AEROLEVANTAMENTO
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) SELECT 6, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active FROM service_type_blocks WHERE service_type_id = 1;
-- SERVIÇO 7: CADASTRO AMBIENTAL RURAL
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) SELECT 7, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active FROM service_type_blocks WHERE service_type_id = 1;
-- SERVIÇO 8: RETIFICAÇÃO ADMINISTRATIVA (LEGADO)
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) SELECT 8, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active FROM service_type_blocks WHERE service_type_id = 1;
-- SERVIÇO 9: UNIFICAÇÃO DE ÁREA
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) SELECT 9, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active FROM service_type_blocks WHERE service_type_id = 1;
-- SERVIÇO 10: LOTEAMENTO URBANO
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) SELECT 10, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active FROM service_type_blocks WHERE service_type_id = 1;
-- SERVIÇO 24: REVISÃO
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) SELECT 24, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active FROM service_type_blocks WHERE service_type_id = 1;
-- SERVIÇO 25: GEO INCRA
INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `is_required`, `default_content`, `allowed_vars`, `is_active`) SELECT 25, block_slug, block_title, category, display_order, is_required, default_content, allowed_vars, is_active FROM service_type_blocks WHERE service_type_id = 3; 

