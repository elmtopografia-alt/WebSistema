-- Tabela de Documentos/Arquivos
CREATE TABLE Documentos (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    id_proposta INT NOT NULL,
    id_cliente INT NOT NULL,
    id_usuario INT NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL, -- nome único no servidor
    tipo_arquivo VARCHAR(100) NOT NULL, -- mime type
    categoria ENUM('proposta', 'contrato', 'comprovante', 'nota_fiscal', 'documento_cliente', 'outro') DEFAULT 'outro',
    tamanho_bytes BIGINT NOT NULL,
    caminho VARCHAR(500) NOT NULL,
    descricao TEXT NULL,
    is_principal BOOLEAN DEFAULT FALSE, -- Para capa/destaque
    data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_modificacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_proposta) REFERENCES Propostas(id_proposta) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id),
    
    INDEX idx_proposta (id_proposta),
    INDEX idx_categoria (categoria),
    INDEX idx_upload (data_upload)
);

-- Tabela de Assinaturas Digitais (opcional, para futuro)
CREATE TABLE Assinaturas_Documentos (
    id_assinatura INT AUTO_INCREMENT PRIMARY KEY,
    id_documento INT NOT NULL,
    id_usuario_assinou INT NULL, -- NULL = assinatura externa
    nome_assinante VARCHAR(255) NOT NULL,
    email_assinante VARCHAR(255) NOT NULL,
    ip_assinatura VARCHAR(45) NOT NULL,
    data_assinatura DATETIME DEFAULT CURRENT_TIMESTAMP,
    hash_verificacao VARCHAR(64) NOT NULL,
    status ENUM('pendente', 'assinado', 'recusado') DEFAULT 'pendente',
    
    FOREIGN KEY (id_documento) REFERENCES Documentos(id_documento) ON DELETE CASCADE
);