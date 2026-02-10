-- Tabela de Histórico de Interações (Timeline unificada)
CREATE TABLE Historico_Interacoes (
    id_historico INT AUTO_INCREMENT PRIMARY KEY,
    id_proposta INT NOT NULL,
    id_cliente INT NOT NULL, -- Denormalizado para facilitar buscas por cliente
    tipo VARCHAR(50) NOT NULL, -- 'status_change', 'tarefa_criada', 'tarefa_concluida', 'email_enviado', 'whatsapp_enviado', 'nota_adicionada', 'arquivo_anexado', 'reuniao_agendada'
    conteudo TEXT NOT NULL,
    metadata JSON NULL, -- Dados extras (ex: arquivo anexado, email destinatário)
    canal VARCHAR(20) NULL, -- 'sistema', 'email', 'whatsapp', 'ligacao'
    id_usuario INT NOT NULL,
    data_interacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_proposta) REFERENCES Propostas(id_proposta),
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id),
    
    INDEX idx_proposta_data (id_proposta, data_interacao),
    INDEX idx_cliente_data (id_cliente, data_interacao),
    INDEX idx_usuario_data (id_usuario, data_interacao),
    INDEX idx_tipo_data (tipo, data_interacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;