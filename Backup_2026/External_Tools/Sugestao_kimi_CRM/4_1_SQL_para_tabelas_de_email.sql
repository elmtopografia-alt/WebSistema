-- Tabela de Templates de Email
CREATE TABLE Email_Templates (
    id_template INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    assunto VARCHAR(255) NOT NULL,
    corpo TEXT NOT NULL,
    variaveis JSON DEFAULT '["{nome_cliente}", "{nome_empresa}", "{valor_proposta}", "{data_atual}"]',
    tipo VARCHAR(50) DEFAULT 'personalizado', -- 'boas_vindas', 'follow_up', 'proposta_enviada', 'cobranca', 'personalizado'
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id)
);

-- Tabela de Envios de Email
CREATE TABLE Email_Envios (
    id_envio INT AUTO_INCREMENT PRIMARY KEY,
    id_proposta INT NOT NULL,
    id_cliente INT NOT NULL,
    id_template INT NULL,
    id_usuario INT NOT NULL,
    assunto VARCHAR(255) NOT NULL,
    corpo TEXT NOT NULL,
    destinatario VARCHAR(255) NOT NULL,
    status ENUM('pendente', 'enviado', 'erro', 'aberto', 'clicado') DEFAULT 'pendente',
    erro_msg TEXT NULL,
    data_agendamento DATETIME NULL,
    data_envio DATETIME NULL,
    data_abertura DATETIME NULL,
    ip_abertura VARCHAR(45) NULL,
    user_agent_abertura TEXT NULL,
    hash_rastreamento VARCHAR(64) UNIQUE NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_proposta) REFERENCES Propostas(id_proposta),
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente),
    FOREIGN KEY (id_template) REFERENCES Email_Templates(id_template),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id),
    
    INDEX idx_status_data (status, data_agendamento),
    INDEX idx_hash (hash_rastreamento),
    INDEX idx_proposta (id_proposta)
);

-- Tabela de Anexos de Email
CREATE TABLE Email_Anexos (
    id_anexo INT AUTO_INCREMENT PRIMARY KEY,
    id_envio INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(500) NOT NULL,
    tamanho_bytes INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_envio) REFERENCES Email_Envios(id_envio) ON DELETE CASCADE
);

-- Templates padrão (inserir após criar tabela)
INSERT INTO Email_Templates (id_usuario, nome, assunto, corpo, tipo) VALUES
(1, 'Boas-vindas', 'Bem-vindo, {nome_cliente}!', 'Olá {nome_cliente},\n\nÉ um prazer ter você conosco. Preparamos uma proposta especial para {nome_empresa} no valor de {valor_proposta}.\n\nAguardo seu retorno.\n\nAtenciosamente,', 'boas_vindas'),
(1, 'Follow-up Proposta', 'Seguimento - Proposta #{id_proposta}', 'Olá {nome_cliente},\n\nGostaria de saber se teve chance de analisar nossa proposta de {valor_proposta}.\n\nEstou à disposição para esclarecer qualquer dúvida.\n\nAbraço,', 'follow_up'),
(1, 'Cobrança Amigável', 'Lembrete de Pagamento', 'Olá {nome_cliente},\n\nPassando para lembrar sobre o pagamento pendente referente à proposta #{id_proposta}.\n\nValor: {valor_proposta}\n\nPodemos agendar?', 'cobranca');