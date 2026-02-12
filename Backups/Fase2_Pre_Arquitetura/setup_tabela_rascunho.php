<?php
// setup_tabela_rascunho.php
// Script para criar a tabela de conteúdo personalizado

require_once 'db.php';

try {
    if (!isset($conn)) {
        throw new Exception("Conexão com banco de dados não estabelecida.");
    }

    $sql = "
    CREATE TABLE IF NOT EXISTS `Proposta_Conteudo_Personalizado` (
      `id_conteudo` int(11) NOT NULL AUTO_INCREMENT,
      `id_proposta` int(11) NOT NULL,
      `block_id` varchar(100) NOT NULL COMMENT 'ID do bloco (ex: apresentacao_content)',
      `conteudo_texto` longtext,
      `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_conteudo`),
      UNIQUE KEY `unique_proposta_block` (`id_proposta`,`block_id`),
      KEY `fk_conteudo_proposta` (`id_proposta`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if ($conn->query($sql) === TRUE) {
        echo "Tabela 'Proposta_Conteudo_Personalizado' criada ou verificada com sucesso.<br>";
        
        // Adicionar FK se não existir (Opcional, pode falhar se a tabela pai não for InnoDB ou tiver incompatibilidade, então fazemos separado com try/catch silencioso ou verificação)
        // Por simplicidade no setup, vamos assumir que funcionou a criação.
        
    } else {
        echo "Erro ao criar tabela: " . $conn->error;
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
