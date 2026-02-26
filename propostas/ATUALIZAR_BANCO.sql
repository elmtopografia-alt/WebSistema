-- Execute este SQL no seu banco de dados para criar os campos necessários
-- Compatível com o EquipamentosMapper e o novo gerador

ALTER TABLE Propostas 
ADD COLUMN IF NOT EXISTS marca_veiculo VARCHAR(100) AFTER valor_final_proposta,
ADD COLUMN IF NOT EXISTS modelo_veiculo VARCHAR(100) AFTER marca_veiculo,
ADD COLUMN IF NOT EXISTS marca_estacao_total VARCHAR(100) AFTER modelo_veiculo,
ADD COLUMN IF NOT EXISTS modelo_estacao_total VARCHAR(100) AFTER marca_estacao_total,
ADD COLUMN IF NOT EXISTS marca_gps VARCHAR(100) AFTER modelo_estacao_total,
ADD COLUMN IF NOT EXISTS modelo_gps VARCHAR(100) AFTER marca_gps,
ADD COLUMN IF NOT EXISTS marca_drone VARCHAR(100) AFTER modelo_gps,
ADD COLUMN IF NOT EXISTS modelo_drone VARCHAR(100) AFTER marca_drone;

-- Verificação (opcional)
-- DESCRIBE Propostas;
