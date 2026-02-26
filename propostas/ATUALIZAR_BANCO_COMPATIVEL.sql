-- Versão compatível com MySQL antigo (sem IF NOT EXISTS)
-- Se der erro de "Duplicate column", é porque já existe. Pode ignorar.

ALTER TABLE Propostas ADD COLUMN marca_veiculo VARCHAR(100) AFTER valor_final_proposta;
ALTER TABLE Propostas ADD COLUMN modelo_veiculo VARCHAR(100) AFTER marca_veiculo;
ALTER TABLE Propostas ADD COLUMN marca_estacao_total VARCHAR(100) AFTER modelo_veiculo;
ALTER TABLE Propostas ADD COLUMN modelo_estacao_total VARCHAR(100) AFTER marca_estacao_total;
ALTER TABLE Propostas ADD COLUMN marca_gps VARCHAR(100) AFTER modelo_estacao_total;
ALTER TABLE Propostas ADD COLUMN modelo_gps VARCHAR(100) AFTER marca_gps;
ALTER TABLE Propostas ADD COLUMN marca_drone VARCHAR(100) AFTER modelo_gps;
ALTER TABLE Propostas ADD COLUMN modelo_drone VARCHAR(100) AFTER marca_drone;
