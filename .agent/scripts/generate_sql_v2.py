
import json

# Output file
output_file = "setup_service_table_v2.sql"

# Common Variables
v_empresa = '["Empresa"]'
v_cidade_data = '["Cidade","DataExtenso"]'
v_header = '["Cidade","DataExtenso","numero_proposta"]'
v_finalidade = '["finalidade"]'
v_area = '["area_obra"]'
v_area_finalidade = '["area_obra","finalidade"]'
v_inv_pag = '["ValorProposta","ValorExtenso"]'
v_cond_pag = '["mobilizacao_percentual","mobilizacao_valor","restante_percentual","restante_valor"]'
v_banco = '["Banco","Agencia","Conta","PIX"]'
v_rodape = '["Empresa","CNPJ","whatsapp"]'
v_prazo = '["prazo_execucao","dias_campo","dias_escritorio"]'

# Service IDs
ID_DRONE = 19
ID_TOPOGRAFIA = 13
ID_DESDOBRAMENTO = 20
ID_USUCAPIAO = 11

# Content Definitions
content_drone = [
    # (slug, title, category, order, content, vars)
    ('cabecalho', 'Cabeçalho', 'layout', 0, None, v_header),
    ('apresentacao_drone', '1. Apresentação', 'presentation', 1, 
     '<p>A <strong>${Empresa}</strong> apresenta esta proposta técnica para execução de <strong>Levantamento Planialtimétrico via Aerofotogrametria com Drones (VANTs)</strong>.</p><p>Trata-se de <strong>Engenharia de Precisão</strong>, não simples filmagem aérea. Geramos representação digital fiel do terreno com coordenadas exatas (Lat, Long, Altitude) para base legal de projetos.</p>', v_empresa),
    ('finalidade_drone', '2. Finalidade', 'presentation', 2,
     '<p>Obter mapeamento aéreo detalhado, ortomosaicos georreferenciados, Modelo Digital de Terreno (MDT) e nuvens de pontos densas para análise, planejamento e cálculo de volumes.</p>', v_area_finalidade),
    ('escopo_drone', '3. Escopo do Serviço', 'technical', 3,
     '<h4>Área de Abrangência</h4><p>Levantamento fotogramétrico da área de <strong>${area_obra}</strong>, compreendendo:</p><ul><li>Captura de imagens aéreas com sobreposição longitudinal 80% e lateral 70%;</li><li>Implantação de pontos de controle terrestre (GCPs) com GPS RTK;</li><li>Processamento fotogramétrico e geração de ortomosaico;</li><li>Extração de curvas de nível e elementos vetoriais.</li></ul>', v_area),
    ('metodologia_drone', '4. Metodologia', 'technical', 4,
     '<h4>Etapa 1: Planejamento de Voo (Escritório)</h4><p>Estudo prévio da área via satélite, definição de altura de voo (GSD desejado) e programação da grade de cobertura.</p><h4>Etapa 2: Apoio Terrestre - GCPs (Campo)</h4><p>Distribuição de alvos no terreno e coleta de coordenadas com GPS Geodésico de alta precisão (RTK). Estes pontos "ancoram" o ortomosaico com precisão centimétrica.</p><h4>Etapa 3: Execução do Voo (Campo)</h4><p>Checklist de segurança (DECEA, baterias, meteorologia). O drone executa rota autônoma capturando imagens em ângulos nadir e oblíquos.</p><h4>Etapa 4: Processamento Fotogramétrico (Escritório)</h4><p>Software especializado: alinhamento de fotos, geração da nuvem de pontos densa, georreferenciamento com GCPs e ortorretificação.</p><h4>Etapa 5: Vetorização e CAD (Escritório)</h4><p>Desenhista vetoriza elementos do terreno (construções, cercas, vegetação) e gera curvas de nível no software CAD.</p>', '[]'),
    ('documentacao_drone', '5. Documentação Gerada', 'technical', 5,
     '<ul><li><strong>Ortomosaico Georreferenciado:</strong> Imagem raster em alta resolução (TIF/JPG) com arquivo de georreferenciamento (TFW/JGW);</li><li><strong>MDT (Modelo Digital de Terreno):</strong> Superfície 3D do solo nu em formato compatível com softwares de terraplenagem;</li><li><strong>Curvas de nível:</strong> Arquivo CAD (DWG/DXF) com equidistância conforme especificação;</li><li><strong>Planta Topográfica:</strong> PDF técnico com legenda, escala, norte e memorial;</li><li><strong>Relatório de Processamento:</strong> Estatísticas de precisão (erro médio quadrado);</li><li><strong>ART:</strong> Anotação de Responsabilidade Técnica registrada no CREA.</li></ul>', '[]'),
    ('equipamentos_drone', '6. Equipamentos', 'technical', 6,
     '<ul><li><strong>Drone:</strong> ${Drone} (câmera de alta resolução, RTK embutido);</li><li><strong>GPS de Apoio:</strong> ${GPS} (coleta de GCPs com precisão centimétrica);</li><li><strong>Estação Total:</strong> ${Estacao_Total} (verificação de pontos de controle);</li><li><strong>Veículo:</strong> ${Veiculo} (transporte da equipe e equipamentos).</li></ul>', '["Drone","GPS","Estacao_Total","Veiculo"]'),
    ('cronograma_drone', '7. Cronograma', 'technical', 7,
     '<table class="proposal-table"><thead><tr><th>Etapa</th><th>Descrição</th><th>Prazo</th></tr></thead><tbody><tr><td>Mobilização</td><td>Planejamento e deslocamento</td><td>Até 02 dias</td></tr><tr><td>Campo</td><td>Instalação de GCPs e voos</td><td>${dias_campo} dia(s)</td></tr><tr><td>Processamento</td><td>Geração de nuvem e ortomosaico</td><td>03 a 05 dias</td></tr><tr><td>Vetorização</td><td>CAD e planta final</td><td>${dias_escritorio} dia(s)</td></tr><tr class="total"><td colspan="2"><strong>TOTAL ESTIMADO</strong></td><td><strong>${prazo_execucao}</strong></td></tr></tbody></table>', v_prazo),
    ('investimento', '8. Investimento', 'financial', 8, None, v_inv_pag),
    ('condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, None, v_cond_pag),
    ('dados_bancarios', '10. Dados Bancários', 'financial', 10, None, v_banco),
    ('consideracoes', '11. Considerações Finais', 'legal', 11, None, v_empresa),
    ('rodape', 'Rodapé', 'layout', 99, None, v_rodape)
]

content_topografia = [
    ('cabecalho', 'Cabeçalho', 'layout', 0, None, v_header),
    ('apresentacao_padrao', '1. Apresentação', 'presentation', 1,
     '<p>A <strong>${Empresa}</strong> é empresa especializada em soluções de Engenharia e Topografia, atuando com equipamentos de alta tecnologia e equipe técnica qualificada.</p><p>Nosso objetivo é fornecer dados precisos e confiáveis, seguindo rigorosamente as normas técnicas da ABNT NBR 13.133.</p>', v_empresa),
    ('finalidade_padrao', '2. Finalidade', 'presentation', 2, '<p>${finalidade}</p>', v_finalidade),
    ('escopo_planialtimetrico', '3. Escopo do Serviço', 'technical', 3,
     '<h4>Levantamento Planialtimétrico Cadastral</h4><p>Execução de levantamento topográfico da área de <strong>${area_obra}</strong>, compreendendo:</p><ul><li>Demarcação do perímetro do imóvel;</li><li>Levantamento de todas as edificações, cercas, muros e benfeitorias;</li><li>Coleta de pontos cotados para geração de curvas de nível;</li><li>Identificação de elementos da rede de infraestrutura (postes, caixas, etc.).</li></ul>', v_area),
    ('metodologia_padrao', '4. Metodologia', 'technical', 4,
     '<h4>Etapa 1: Reconhecimento e Amarração Geodésica</h4><p>Implantação da rede de apoio com GPS GNSS de dupla frequência, vinculada ao Sistema Geodésico Brasileiro (SIRGAS 2000).</p><h4>Etapa 2: Levantamento de Detalhe</h4><p>Utilização de Estação Total para coleta irradiada de todos os elementos do terreno. Coleta de pontos para altimetria.</p><h4>Etapa 3: Processamento e CAD</h4><p>Processamento dos dados brutos, cálculos de poligonais, ajustamento e geração do desenho técnico em CAD.</p>', '[]'),
    ('documentacao_padrao', '5. Documentação Gerada', 'technical', 5,
     '<ul><li>Planta Topográfica Planialtimétrica (PDF e DWG);</li><li>Memorial Descritivo do perímetro;</li><li>ART registrada no CREA;</li><li>Relatório Fotográfico (quando aplicável).</li></ul>', '[]'),
    ('equipamentos_padrao', '6. Equipamentos', 'technical', 6,
     '<ul><li><strong>Estação Total:</strong> ${Estacao_Total};</li><li><strong>GPS/GNSS:</strong> ${GPS} (amarração geodésica);</li><li><strong>Veículo:</strong> ${Veiculo}.</li></ul>', '["Estacao_Total","GPS","Veiculo"]'),
    ('cronograma_padrao', '7. Prazo de Execução', 'technical', 7,
     '<p>O serviço será executado em <strong>${prazo_execucao}</strong>, divididos em:</p><ul><li><strong>Campo:</strong> ${dias_campo} dia(s) para coleta de dados;</li><li><strong>Escritório:</strong> ${dias_escritorio} dia(s) para processamento e desenho.</li></ul>', v_prazo),
    ('investimento', '8. Investimento', 'financial', 8, None, v_inv_pag),
    ('condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, None, '["mobilizacao_percentual","mobilizacao_valor"]'),
    ('dados_bancarios', '10. Dados Bancários', 'financial', 10, None, v_banco),
    ('consideracoes', '11. Considerações Finais', 'legal', 11, None, v_empresa),
    ('rodape', 'Rodapé', 'layout', 99, None, '["Empresa","CNPJ"]')
]

content_usucapiao = [
    ('cabecalho', 'Cabeçalho', 'layout', 0, None, v_cidade_data),
    ('apresentacao_usucapiao', '1. Apresentação', 'presentation', 1,
     '<p>A <strong>${Empresa}</strong> presta serviços de topografia legal para processos de usucapião, fornecendo documentação técnica exigida pelo Poder Judiciário e cartórios de registro de imóveis.</p>', v_empresa),
    ('finalidade_usucapiao', '2. Finalidade', 'presentation', 2,
     '<p>Fornecer a delimitação precisa do imóvel, com coordenadas georreferenciadas, para comprovar a <strong>posse contínua e pacífica</strong> e demonstrar a área ocupada no processo legal de usucapião.</p>', '[]'),
    ('escopo_usucapiao', '3. Escopo do Serviço', 'technical', 3,
     '<p>Levantamento planialtimétrico da área de <strong>${area_obra}</strong> com ênfase em:</p><ul><li>Identificação precisa dos limites e confrontantes;</li><li>Relação de benfeitorias existentes (casas, cercas, plantações);</li><li>Tempo de ocupação comprovado (quando houver documentos);</li><li>Memorial descritivo detalhado para embasar ação judicial.</li></ul>', v_area),
    ('metodologia_usucapiao', '4. Metodologia', 'technical', 4,
     '<p>Levantamento topográfico com GPS RTK e Estação Total, georreferenciado ao SIRGAS 2000. Prioridade à identificação de confrontantes e registro fotográfico datado.</p>', '[]'),
    ('documentacao_usucapiao', '5. Documentação para Processo Judicial', 'legal', 5,
     '<ul><li><strong>Planta Topográfica Georreferenciada:</strong> Com coordenadas dos vértices;</li><li><strong>Memorial Descritivo Detalhado:</strong> Com confrontantes e benfeitorias;</li><li><strong>Relatório Fotográfico:</strong> Fotos datadas do terreno e acessos;</li><li><strong>ART Específica:</strong> Com menção ao processo de usucapião;</li><li><strong>Declaração Técnica:</strong> Sobre a ocupação do imóvel (se solicitado).</li></ul>', '[]'),
    ('investimento', '8. Investimento', 'financial', 8, None, v_inv_pag),
    ('condicoes_pagamento', '9. Condições de Pagamento', 'financial', 9, None, v_cond_pag),
    ('dados_bancarios', '10. Dados Bancários', 'financial', 10, None, v_banco),
    ('consideracoes', '11. Considerações Finais', 'legal', 11, None, v_empresa),
    ('rodape', 'Rodapé', 'layout', 99, None, v_rodape)
]

# Desdobramento uses Topografia base but changes Metodologia
content_desdobramento = []
for block in content_topografia:
    slug, title, cat, order, content, vars_ = block
    if slug == 'metodologia_padrao':
        new_content = '<h4>Etapa 1 a 3: Idênticas ao Levantamento Planialtimétrico</h4><p>Execução completa do levantamento planialtimétrico cadastral da área original.</p><h4>Etapa 4: Projeto de Loteamento (Escritório)</h4><p>Elaboração do projeto de divisão em lotes menores, respeitando zoneamento, áreas de preservação e acessos.</p><h4>Etapa 5: Locação das Novas Divisas (Campo)</h4><p><strong>Esta é a diferença crucial:</strong> Após aprovação do projeto preliminar, nossa equipe retorna ao campo para <strong>marcar fisicamente no terreno</strong> as novas divisas dos lotes com estacas de madeira ou pintura, permitindo que o cliente visualize o resultado antes da documentação final.</p><h4>Etapa 6: Documentação para Registro</h4><p>Memoriais descritivos individuais por lote, plantas de situação e processo de desdobro para cartório/prefeitura.</p>'
        content_desdobramento.append(('metodologia_desdobramento', '4. Metodologia', cat, order, new_content, vars_))
    else:
        content_desdobramento.append(block)


# Generate SQL
sql = """
DROP TABLE IF EXISTS `service_type_blocks`;
CREATE TABLE `service_type_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_type_id` int(11) NOT NULL COMMENT 'FK para Tipo_Servicos.id_servico',
  `block_slug` varchar(50) NOT NULL COMMENT 'Identificador do bloco',
  `block_title` varchar(100) NOT NULL COMMENT 'Título exibido no editor',
  `category` enum('layout','presentation','technical','financial','legal') DEFAULT 'technical',
  `display_order` int(11) DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 1,
  `default_content` longtext COMMENT 'Conteúdo HTML padrão para este serviço',
  `allowed_vars` json COMMENT 'Variáveis permitidas',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_service_block` (`service_type_id`, `block_slug`),
  KEY `idx_service_order` (`service_type_id`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `service_type_blocks` (`service_type_id`, `block_slug`, `block_title`, `category`, `display_order`, `default_content`, `allowed_vars`) VALUES
"""

values = []

def add_service(svc_id, blocks):
    for slug, title, cat, order, content, vars_ in blocks:
        c_val = f"'{content}'" if content else "NULL"
        row = f"({svc_id}, '{slug}', '{title}', '{cat}', {order}, {c_val}, '{vars_}')"
        values.append(row)

# Add predefined services
add_service(ID_DRONE, content_drone)
add_service(ID_TOPOGRAFIA, content_topografia)
add_service(ID_USUCAPIAO, content_usucapiao)
add_service(ID_DESDOBRAMENTO, content_desdobramento)

# Add generic services (mapped to Topografia for now)
generic_ids = [12, 14, 15, 16, 17, 18, 21] 
for gid in generic_ids:
    add_service(gid, content_topografia)

sql += ",\n".join(values) + ";"

with open(output_file, "w", encoding="utf-8") as f:
    f.write(sql)

print(f"File {output_file} generated successfully.")
