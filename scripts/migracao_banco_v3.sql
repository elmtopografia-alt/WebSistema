-- 
-- MIGRAÇÃO DE BANCO - PADRÃO ÚNICO v3.0
-- Este script padroniza os nomes das colunas na tabela 'Propostas'
-- para que coincidam exatamente com as chaves dos modelos DOCX.
--

-- 1. Renomeação de Colunas Existentes
ALTER TABLE Propostas CHANGE COLUMN empresa_proponente_nome empresa_nome VARCHAR(255);
ALTER TABLE Propostas CHANGE COLUMN valor_final_proposta valor_total DECIMAL(15,2);
ALTER TABLE Propostas CHANGE COLUMN Valor_proposta_extenso valor_total_extenso TEXT;

-- 2. Garantia de Existência das Colunas v3.0 (Dados denormalizados para historical record)
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS email_cliente VARCHAR(255) AFTER id_cliente;
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS whatsapp_cliente VARCHAR(20) AFTER email_cliente;
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS telefone_cliente VARCHAR(20) AFTER whatsapp_cliente;
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS nome_cliente VARCHAR(255) AFTER id_cliente;

-- 3. Dados da Obra
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS endereco_obra TEXT;
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS cidade_obra VARCHAR(100);
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS estado_obra VARCHAR(2);
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS finalidade_obra TEXT;

-- 4. Valores Adicionais
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS valor_entrada DECIMAL(15,2);
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS valor_restante DECIMAL(15,2);

-- 5. Dados Bancários no Snapshot da Proposta (Importante para auditoria posterior)
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS banco_nome VARCHAR(100);
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS banco_agencia VARCHAR(20);
ALTER TABLE Propostas ADD COLUMN IF NOT EXISTS banco_conta VARCHAR(20);

-- FIM DA MIGRAÇÃO v3.0
