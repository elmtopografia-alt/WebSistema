-- ============================================================
-- VERIFICAÇÃO PÓS-MIGRAÇÃO SGT PROPOSTAS DOCX
-- Versão: 3.0.0-FINAL | Data: 2024
-- Status: Validação completa de saúde do sistema
-- ============================================================

-- ============================================================
-- SEÇÃO 1: VERIFICAÇÃO ESTRUTURAL (Schema)
-- ============================================================

-- 1.1 Confirmação de colunas DOCX na tabela propostas
SELECT 
    'ESTRUTURA' as secao,
    COLUMN_NAME as coluna,
    DATA_TYPE as tipo,
    IS_NULLABLE as permite_null,
    COLUMN_DEFAULT as padrao,
    COLUMN_COMMENT as descricao,
    CASE 
        WHEN COLUMN_NAME = 'modelo_docx' THEN 'REFERENCIA_MODELO'
        WHEN COLUMN_NAME = 'docx_conteudo' THEN 'DADOS_SERIALIZADOS'
        WHEN COLUMN_NAME = 'docx_blocos_count' THEN 'METADADO_CONTAGEM'
        WHEN COLUMN_NAME = 'docx_ultima_edicao' THEN 'TRACKING_TEMPORAL'
    END as funcao
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'propostas'
AND COLUMN_NAME IN ('modelo_docx', 'docx_conteudo', 'docx_blocos_count', 'docx_ultima_edicao')
ORDER BY ORDINAL_POSITION;

-- 1.2 Verificação de índices criados
SELECT 
    'INDEX' as secao,
    INDEX_NAME as nome_indice,
    COLUMN_NAME as coluna_indexada,
    NON_UNIQUE as permite_duplicados
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'propostas'
AND INDEX_NAME = 'idx_modelo_docx';

-- 1.3 Verificação de tabelas auxiliares
SELECT 
    'TABELAS_AUX' as secao,
    TABLE_NAME as tabela,
    TABLE_ROWS as registros,
    ROUND(DATA_LENGTH/1024, 2) as tamanho_kb,
    CREATE_TIME as criada_em,
    TABLE_COMMENT as descricao
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN ('modelos_docx', 'proposta_docx_historico');

-- 1.4 Verificação de triggers
SELECT 
    'TRIGGERS' as secao,
    TRIGGER_NAME as nome,
    EVENT_MANIPULATION as evento,
    ACTION_TIMING as momento,
    ACTION_STATEMENT as acao_resumida
FROM INFORMATION_SCHEMA.TRIGGERS 
WHERE TRIGGER_SCHEMA = DATABASE()
AND TRIGGER_NAME = 'trg_proposta_docx_update';

-- 1.5 Verificação de views
SELECT 
    'VIEWS' as secao,
    TABLE_NAME as nome_view,
    VIEW_DEFINITION as definicao_resumida
FROM INFORMATION_SCHEMA.VIEWS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'view_propostas_docx';

-- ============================================================
-- SEÇÃO 2: VERIFICAÇÃO DE INTEGRIDADE DE DADOS
-- ============================================================

-- 2.1 Contagem geral e comparativo
SELECT 
    'CONSOLIDADO' as secao,
    COUNT(*) as total_propostas,
    SUM(CASE WHEN modelo_docx IS NOT NULL THEN 1 ELSE 0 END) as com_modelo_docx,
    SUM(CASE WHEN docx_conteudo IS NOT NULL THEN 1 ELSE 0 END) as com_conteudo_docx,
    SUM(CASE WHEN modelo_docx IS NULL AND docx_conteudo IS NULL THEN 1 ELSE 0 END) as modo_legacy_puro,
    ROUND(
        SUM(CASE WHEN modelo_docx IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 
        2
    ) as percentual_adocao_docx
FROM propostas
WHERE deleted_at IS NULL; -- ajuste se usar soft delete diferente

-- 2.2 Verificação de integridade: Propostas com modelo_docx mas sem conteudo
SELECT 
    'ANOMALIA_1' as tipo_anomalia,
    id_proposta,
    numero_proposta,
    modelo_docx,
    'Modelo associado mas sem conteudo salvo' as descricao,
    data_criacao as criada_em
FROM propostas
WHERE modelo_docx IS NOT NULL 
AND docx_conteudo IS NULL
ORDER BY data_criacao DESC
LIMIT 10;

-- 2.3 Verificação de integridade: Conteudo JSON inválido
SELECT 
    'ANOMALIA_2' as tipo_anomalia,
    id_proposta,
    numero_proposta,
    'JSON possivelmente corrompido' as descricao,
    LEFT(docx_conteudo, 100) as preview_conteudo
FROM propostas
WHERE docx_conteudo IS NOT NULL
AND (
    JSON_VALID(docx_conteudo) = 0 
    OR docx_conteudo NOT LIKE '[%'
)
LIMIT 10;

-- 2.4 Verificação de integridade: Contagem de blocos inconsistente
SELECT 
    'ANOMALIA_3' as tipo_anomalia,
    id_proposta,
    numero_proposta,
    docx_blocos_count as contagem_registrada,
    JSON_LENGTH(docx_conteudo) as contagem_real,
    'Discrepancia entre contagem e JSON' as descricao
FROM propostas
WHERE docx_conteudo IS NOT NULL
AND docx_blocos_count != JSON_LENGTH(docx_conteudo)
LIMIT 10;

-- 2.5 Verificação de modelos referenciados inexistentes
SELECT 
    'ANOMALIA_4' as tipo_anomalia,
    p.modelo_docx as modelo_referenciado,
    COUNT(*) as quantidade_propostas,
    'Modelo nao catalogado na tabela modelos_docx' as descricao
FROM propostas p
LEFT JOIN modelos_docx m ON p.modelo_docx = m.nome_arquivo
WHERE p.modelo_docx IS NOT NULL
AND m.id IS NULL
GROUP BY p.modelo_docx;

-- ============================================================
-- SEÇÃO 3: VALIDAÇÃO FUNCIONAL (Simulação de uso)
-- ============================================================

-- 3.1 Teste de leitura: Simula o que o PHP faz ao buscar uma proposta DOCX
SELECT 
    'TESTE_LEITURA' as secao,
    id_proposta,
    numero_proposta,
    modelo_docx,
    docx_blocos_count,
    -- Simula json_decode do PHP
    JSON_UNQUOTE(JSON_EXTRACT(docx_conteudo, '$[0].conteudo')) as primeiro_bloco_preview,
    docx_ultima_edicao,
    CASE 
        WHEN docx_ultima_edicao > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'EDITADO_RECENTE'
        WHEN docx_ultima_edicao IS NOT NULL THEN 'EDITADO_ANTERIORMENTE'
        ELSE 'SEM_EDICOES'
    END as status_edicao
FROM propostas
WHERE modelo_docx IS NOT NULL
ORDER BY docx_ultima_edicao DESC NULLS LAST
LIMIT 5;

-- 3.2 Teste de escrita: Validação de constraints para INSERT simulado
SELECT 
    'TESTE_ESCRITA' as secao,
    'Validacao de tipos de dados' as teste,
    CASE 
        WHEN MAX(LENGTH(modelo_docx)) > 100 THEN 'ERRO: modelo_docx excede 100 chars'
        ELSE 'OK: modelo_docx dentro do limite'
    END as validacao_modelo,
    CASE 
        WHEN MAX(LENGTH(docx_conteudo)) > 65535 THEN 'AVISO: docx_conteudo grande (LONGTEXT suporta)'
        ELSE 'OK: docx_conteudo tamanho normal'
    END as validacao_conteudo
FROM propostas
WHERE modelo_docx IS NOT NULL OR docx_conteudo IS NOT NULL;

-- 3.3 Teste de performance: Tempo de resposta estimado para consultas DOCX
SELECT 
    'PERFORMANCE' as secao,
    COUNT(*) as total_registros,
    ROUND(
        (SELECT COUNT(*) FROM propostas WHERE modelo_docx IS NOT NULL) * 100.0 / COUNT(*),
        2
    ) as percentual_docx,
    CASE 
        WHEN COUNT(*) > 10000 AND (SELECT COUNT(*) FROM propostas WHERE modelo_docx IS NOT NULL) > 1000 
        THEN 'RECOMENDADO: Adicionar cache ou paginacao'
        ELSE 'OK: Volume gerenciavel'
    END as recomendacao
FROM propostas;

-- ============================================================
-- SEÇÃO 4: RELATÓRIO DE MODELOS CATALOGADOS
-- ============================================================

-- 4.1 Listagem de modelos disponíveis
SELECT 
    'CATALOGO_MODELOS' as secao,
    m.nome_arquivo,
    m.nome_exibicao,
    m.ativo,
    m.uso_count,
    COUNT(p.id_proposta) as propostas_usando,
    MAX(p.docx_ultima_edicao) as ultima_edicao_em
FROM modelos_docx m
LEFT JOIN propostas p ON m.nome_arquivo = p.modelo_docx
GROUP BY m.id, m.nome_arquivo, m.nome_exibicao, m.ativo, m.uso_count
ORDER BY propostas_usando DESC;

-- 4.2 Modelos gerados em arquivo mas não catalogados (orphans)
SELECT 
    'MODELOS_ORFAOS' as secao,
    p.modelo_docx as nome_arquivo,
    COUNT(*) as usos_detectados,
    MIN(p.data_criacao) as primeira_utilizacao,
    'Sugerir: INSERT INTO modelos_docx' as acao_recomendada
FROM propostas p
LEFT JOIN modelos_docx m ON p.modelo_docx = m.nome_arquivo
WHERE p.modelo_docx IS NOT NULL
AND m.id IS NULL
GROUP BY p.modelo_docx;

-- ============================================================
-- SEÇÃO 5: CHECKLIST DE SAÚDE DO SISTEMA
-- ============================================================

-- 5.1 Score geral de saúde (0-100)
SELECT 
    'SCORE_SAUDE' as secao,
    'Sistema DOCX SGT Propostas' as sistema,
    ROUND(
        (
            -- Estrutura (40 pontos)
            (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = 'propostas' 
             AND COLUMN_NAME IN ('modelo_docx','docx_conteudo','docx_blocos_count','docx_ultima_edicao')) * 10 +
            
            -- Índices (20 pontos)
            (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = 'propostas' 
             AND INDEX_NAME = 'idx_modelo_docx') * 20 +
            
            -- Dados íntegros (40 pontos)
            (SELECT 
                CASE 
                    WHEN COUNT(*) = 0 THEN 40 
                    ELSE GREATEST(0, 40 - (COUNT(*) * 10))
                END 
             FROM propostas 
             WHERE (modelo_docx IS NOT NULL AND docx_conteudo IS NULL)
                OR (docx_conteudo IS NOT NULL AND JSON_VALID(docx_conteudo) = 0)
            )
        ),
        0
    ) as pontuacao_saude_0_100,
    CASE 
        WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'propostas' 
              AND COLUMN_NAME IN ('modelo_docx','docx_conteudo','docx_blocos_count','docx_ultima_edicao')) = 4
             AND (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'propostas' 
                  AND INDEX_NAME = 'idx_modelo_docx') = 1
             AND (SELECT COUNT(*) FROM propostas 
                  WHERE modelo_docx IS NOT NULL AND docx_conteudo IS NULL) = 0
        THEN 'SAUDAVEL - Sistema pronto para producao'
        WHEN (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'propostas' 
              AND COLUMN_NAME IN ('modelo_docx','docx_conteudo','docx_blocos_count','docx_ultima_edicao')) >= 2
        THEN 'FUNCIONAL - Requer atencao em itens pendentes'
        ELSE 'CRITICO - Revisar migracao imediatamente'
    END as status_sistema;

-- 5.2 Resumo executivo para stakeholders
SELECT 
    'RESUMO_EXECUTIVO' as secao,
    NOW() as verificacao_realizada_em,
    DATABASE() as banco_dados,
    VERSION() as versao_mysql,
    (SELECT COUNT(*) FROM propostas) as total_propostas_sistema,
    (SELECT COUNT(DISTINCT modelo_docx) FROM propostas WHERE modelo_docx IS NOT NULL) as modelos_distintos_em_uso,
    (SELECT COUNT(*) FROM modelos_docx) as modelos_catalogados,
    CASE 
        WHEN (SELECT COUNT(*) FROM propostas WHERE modelo_docx IS NOT NULL) > 0 
        THEN 'MIGRACAO_EM_USO'
        ELSE 'MIGRACAO_NAO_INICIADA'
    END as fase_adocao_docx;

-- ============================================================
-- FIM DO SCRIPT DE VERIFICAÇÃO
-- ============================================================
