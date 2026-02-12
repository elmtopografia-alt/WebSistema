<?php
/**
 * TEMPLATE DE MIGRAÇÃO SGT
 * 
 * Use este modelo para migrar páginas legadas.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use SGT\Security\AuthService;
use SGT\Security\CSRFProtection;
use SGT\Security\InputSanitizer;

// 1. Exigir Autenticação
AuthService::requireAuth();

// 2. Processamento POST Seguro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        CSRFProtection::verifyOrFail();
        
        // Exemplo: Limpar um nome vindo do POST
        $nome = InputSanitizer::html($_POST['nome'] ?? '');
        
        flash('success', 'Dados processados com segurança!');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// 3. HTML com Segurança
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página Migrada - SGT</title>
</head>
<body>
    <h1>Exemplo de Migração</h1>
    
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- ESSENCIAL: CSRF em todos os formulários -->
    <form method="POST">
        <?= CSRFProtection::getInputField() ?>
        
        <input type="text" name="nome" placeholder="Digite algo...">
        <button type="submit">Enviar</button>
    </form>

    <script>
        // Token disponível para AJAX
        const sgtToken = '<?= CSRFProtection::getToken() ?>';
    </script>
</body>
</html>
