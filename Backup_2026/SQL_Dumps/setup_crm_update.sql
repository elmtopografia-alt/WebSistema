-- Adicionando colunas para CRM Lean
ALTER TABLE Propostas
ADD COLUMN alert_crm tinyint(1) DEFAULT 0, -- 0=Sem alerta, 1=Alerta Ativo
ADD COLUMN data_ultimo_contato DATETIME DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN previsao_pagamento DATE DEFAULT NULL,
ADD COLUMN status_crm varchar(50) DEFAULT 'Novo', -- Novo, Morno, Quente, Fechamento
ADD COLUMN motivo_perda TEXT DEFAULT NULL,
ADD COLUMN valor_recebido DECIMAL(10,2) DEFAULT 0.00;

-- Atualizar dados antigos para evitar NULLs que quebrem lógica
UPDATE Propostas SET data_ultimo_contato = data_criacao WHERE data_ultimo_contato IS NULL;
UPDATE Propostas SET status_crm = 'Novo' WHERE status_crm IS NULL;
