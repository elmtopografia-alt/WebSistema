<?php
// ARQUIVO: valida_demo.php
// FUNÇÃO: Verifica validade do acesso (Porteiro)
// NOTA: Este arquivo deve existir mesmo na Produção.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica de Expulsão (Só executa se for ambiente DEMO)
if (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') {
    
    // Se tiver data de validade na sessão
    if (isset($_SESSION['validade_demo'])) {
        try {
            $agora = new DateTime(); // Data/Hora atual
            $validade = new DateTime($_SESSION['validade_demo']); // Data limite
            
            // Se AGORA for maior que a VALIDADE -> Expira
            if ($agora > $validade) {
                // Destroi sessão e manda para tela de bloqueio
                session_destroy();
                header("Location: bloqueio_demo.php"); 
                exit;
            }
        } catch (Exception $e) {
            // Se der erro na data, ignora (segurança para não travar produção)
        }
    }
}
// Se for produção, este arquivo termina aqui silenciosamente e deixa o sistema rodar.
?>