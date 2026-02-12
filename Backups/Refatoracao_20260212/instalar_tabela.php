<?php
// instalar_tabela.php
// Script para criar a tabela leads_prospeccao no banco de produção (ou local)
// Usa a conexão inteligente do conexao.php

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h3>Instalador de Tabela de Prospecção</h3>";

try {
    // 1. Obter conexão (já com as credenciais certas do config.php)
    if (!file_exists('conexao.php')) {
        die("Erro: Arquivo conexao.php não encontrado.");
    }
    $pdo = require 'conexao.php';
    
    // Obter nome do banco atual
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    
    echo "<p>Conectado com sucesso ao banco: <strong>$dbName</strong></p>";
    
    // 2. SQL da Tabela
    $sql = "
    CREATE TABLE IF NOT EXISTS leads_prospeccao (
        id INT AUTO_INCREMENT PRIMARY KEY,
        
        -- Identificação do Alvo
        nome_empresa VARCHAR(255) NOT NULL,
        site_origem VARCHAR(255) UNIQUE NOT NULL,
        ramo_atuacao VARCHAR(100),
        
        -- Ouro (Dados de Contato)
        whatsapp VARCHAR(50),
        email_contato VARCHAR(255),
        telefone_fixo VARCHAR(50),
        
        -- Inteligência da Abordagem
        tem_formulario BOOLEAN DEFAULT 0,
        url_formulario VARCHAR(255),
        
        -- Controle do Robô
        status_envio ENUM('PENDENTE', 'ENVIADO', 'FALHA', 'IGNORADO') DEFAULT 'PENDENTE',
        data_captura DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_envio DATETIME,
        
        -- Log de Ética
        metodo_captura VARCHAR(50)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    // 3. Executar
    $pdo->exec($sql);
    echo "<p style='color:green'>✅ Tabela <code>leads_prospeccao</code> criada/verificada com sucesso!</p>";
    
    // 4. Verificar se existe
    $check = $pdo->query("SHOW TABLES LIKE 'leads_prospeccao'");
    if ($check->rowCount() > 0) {
        echo "<p>Confirmação: A tabela existe no banco de dados.</p>";
    } else {
        echo "<p style='color:red'>❌ Erro estranho: O comando rodou mas a tabela não apareceu.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>Erro Fatal: " . $e->getMessage() . "</p>";
}
?>
