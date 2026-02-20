-- ============================================================
-- MIGRAÇÃO SGT PROPOSTAS - SUPORTE A MODELOS DOCX
-- Versão: 3.0.0 | Data: 2024
-- Cuidadoso: Verifica existência antes de alterar
-- ============================================================

-- Desativa verificações de chave estrangeira temporariamente (se necessário)
-- SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TABELA: PROPOSTAS - Adiciona campos DOCX
-- ============================================================

-- Verifica e adiciona coluna modelo_docx (referência ao modelo usado)
SET @coluna_existe = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'propostas' 
    AND COLUMN_NAME = 'modelo_docx'
);

SET @sql = IF(@coluna_existe = 0, 
    'ALTER TABLE propostas ADD COLUMN modelo_docx VARCHAR(100) NULL DEFAULT NULL COMMENT "ID do modelo DOCX ativo"',
    'SELECT "Coluna modelo_docx já existe" AS mensagem'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verifica e adiciona coluna docx_conteudo (JSON dos blocos editados)
SET @coluna_existe = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'propostas' 
    AND COLUMN_NAME = 'docx_conteudo'
);

SET @sql = IF(@coluna_existe = 0,
    'ALTER TABLE propostas ADD COLUMN docx_conteudo LONGTEXT NULL DEFAULT NULL COMMENT "JSON serializado dos blocos DOCX editados"',
    'SELECT "Coluna docx_conteudo já existe" AS mensagem'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verifica e adiciona coluna docx_blocos_count (contagem para validação)
SET @coluna_existe = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'propostas' 
    AND COLUMN_NAME = 'docx_blocos_count'
);

SET @sql = IF(@coluna_existe = 0,
    'ALTER TABLE propostas ADD COLUMN docx_blocos_count INT UNSIGNED DEFAULT 0 COMMENT "Quantidade de blocos no modelo DOCX"',
    'SELECT "Coluna docx_blocos_count já existe" AS mensagem'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verifica e adiciona coluna docx_ultima_edicao (tracking)
SET @coluna_existe = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'propostas' 
    AND COLUMN_NAME = 'docx_ultima_edicao'
);

SET @sql = IF(@coluna_existe = 0,
    'ALTER TABLE propostas ADD COLUMN docx_ultima_edicao TIMESTAMP NULL DEFAULT NULL COMMENT "Última vez que os blocos DOCX foram editados"',
    'SELECT "Coluna docx_ultima_edicao já existe" AS mensagem'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 2. TABELA: MODELOS_DOCX (Opcional - se quiser catalogar modelos)
-- ============================================================

-- Cria tabela de catálogo de modelos apenas se não existir
CREATE TABLE IF NOT EXISTS modelos_docx (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_arquivo VARCHAR(100) NOT NULL COMMENT 'Nome do arquivo PHP gerado (ex: ModeloPropostaDrone)',
    nome_exibicao VARCHAR(150) NULL COMMENT 'Nome amigável para dropdown',
    descricao TEXT NULL,
    variaveis_detectadas JSON NULL COMMENT 'Array de variáveis ${xxx} encontradas no modelo',
    blocos_estrutura JSON NULL COMMENT 'Estrutura de blocos (tipos, ordem)',
    ativo TINYINT(1) DEFAULT 1,
    uso_count INT UNSIGNED DEFAULT 0 COMMENT 'Quantas propostas usam este modelo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_nome_arquivo (nome_arquivo),
    KEY idx_ativo (ativo)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de modelos DOCX gerados pelo sistema';

-- ============================================================
-- 3. TABELA: PROPOSTA_DOCX_HISTORICO (Opcional - versionamento)
-- ============================================================

-- Cria tabela de histórico de edições DOCX apenas se não existir
CREATE TABLE IF NOT EXISTS proposta_docx_historico (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proposta_id INT UNSIGNED NOT NULL,
    docx_conteudo LONGTEXT NOT NULL COMMENT 'Snapshot dos blocos nesta versão',
    editado_por INT UNSIGNED NULL COMMENT 'ID do usuário',
    motivo VARCHAR(255) NULL COMMENT 'Motivo da edição (opcional)',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    KEY idx_proposta (proposta_id),
    KEY idx_data (criado_em),
    
    CONSTRAINT fk_historico_proposta 
        FOREIGN KEY (proposta_id) 
        REFERENCES propostas(id) 
        ON DELETE CASCADE
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Histórico de versões de conteúdo DOCX (para rollback)';

-- ============================================================
-- 4. ÍNDICES OTIMIZADOS
-- ============================================================

-- Índice para buscar propostas por modelo DOCX (se coluna foi criada)
SET @index_existe = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'propostas' 
    AND INDEX_NAME = 'idx_modelo_docx'
);

SET @sql = IF(@index_existe = 0,
    'CREATE INDEX idx_modelo_docx ON propostas(modelo_docx)',
    'SELECT "Índice idx_modelo_docx já existe" AS mensagem'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 5. DADOS INICIAIS (Opcional)
-- ============================================================

-- Insere registro de modelo padrão se a tabela estiver vazia
INSERT INTO modelos_docx (nome_arquivo, nome_exibicao, descricao, ativo)
SELECT * FROM (SELECT 'Padrao' as nome_arquivo, 'Modelo Padrão SGT' as nome_exibicao, 'Modelo tradicional do sistema' as descricao, 1 as ativo) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM modelos_docx WHERE nome_arquivo = 'Padrao'
) LIMIT 1;

-- ============================================================
-- 6. VIEW DE CONSULTA (Opcional - facilita relatórios)
-- ============================================================

CREATE OR REPLACE VIEW view_propostas_docx AS
SELECT 
    p.id,
    p.numero_proposta,
    p.nome_cliente,
    p.modelo_docx,
    p.docx_blocos_count,
    CASE 
        WHEN p.modelo_docx IS NOT NULL THEN 'DOCX'
        ELSE 'LEGACY'
    END as tipo_modelo,
    CASE
        WHEN p.docx_conteudo IS NOT NULL THEN 'EDITADO'
        WHEN p.modelo_docx IS NOT NULL THEN 'MODELO_ATIVO'
        ELSE 'PADRAO'
    END as status_docx,
    p.docx_ultima_edicao
FROM propostas p
WHERE p.deleted_at IS NULL; -- Se usar soft delete

-- ============================================================
-- 7. TRIGGER DE AUDITORIA (Opcional - avançado)
-- ============================================================

DELIMITER //

-- Trigger para atualizar docx_ultima_edicao automaticamente
DROP TRIGGER IF EXISTS trg_proposta_docx_update;

CREATE TRIGGER trg_proposta_docx_update 
BEFORE UPDATE ON propostas
FOR EACH ROW
BEGIN
    -- Se o conteúdo DOCX mudou, atualiza o timestamp
    IF OLD.docx_conteudo <> NEW.docx_conteudo THEN
        SET NEW.docx_ultima_edicao = NOW();
    END IF;
END//

DELIMITER ;

-- ============================================================
-- 8. VERIFICAÇÃO FINAL
-- ============================================================

-- Relatório de colunas criadas
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'propostas'
AND COLUMN_NAME IN ('modelo_docx', 'docx_conteudo', 'docx_blocos_count', 'docx_ultima_edicao')
ORDER BY ORDINAL_POSITION;

-- Reativa verificações
-- SET FOREIGN_KEY_CHECKS = 1;
