-- setup_leads_prospeccao.sql
CREATE TABLE IF NOT EXISTS leads_prospeccao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_empresa VARCHAR(255) NOT NULL,
    site_origem VARCHAR(255) UNIQUE NOT NULL,
    ramo_atuacao VARCHAR(100),
    whatsapp VARCHAR(20),
    email_contato VARCHAR(255),
    tem_formulario BOOLEAN DEFAULT 0,
    status_envio ENUM('PENDENTE','ENVIADO','FALHA','IGNORADO') DEFAULT 'PENDENTE',
    metodo_captura ENUM('public_form','wa_link','email_publico') NOT NULL,
    data_captura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_envio TIMESTAMP NULL,
    tentativas TINYINT UNSIGNED DEFAULT 0,
    
    INDEX idx_status (status_envio),
    INDEX idx_metodo (metodo_captura),
    INDEX idx_data (data_captura)
) ENGINE=InnoDB;
