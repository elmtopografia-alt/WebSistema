<?php
// install_db_update.php
require_once 'config.php';
require_once 'db.php';

$conn = Database::getProd();
$msg = "";
$error = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute'])) {
    $files = [
        'setup_service_part1.sql',
        'setup_service_part2.sql',
        'setup_service_part3.sql',
        'setup_service_part4.sql'
    ];

    try {
        // Database::getProd() returns a mysqli object, not PDO
        $conn->begin_transaction();
        
        // Disable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($files as $file) {
            if (!file_exists($file)) {
                $error .= "Arquivo $file não encontrado.<br>";
                continue;
            }
            
            $sqlContent = file_get_contents($file);
            
            // Execute multiple queries at once (handles ; inside strings correctly)
            if ($conn->multi_query($sqlContent)) {
                do {
                    // consume results to clear buffer
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
                $msg .= "Arquivo $file processado.<br>";
            } else {
                 $error .= "Erro em $file: " . $conn->error . "<br>";
            }
        }

        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $conn->commit();
        $msg .= "<strong>ATUALIZAÇÃO CONCLUÍDA!</strong>";
    
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Erro Crítico: " . $e->getMessage();
    }
}


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Instalador de Atualização de BD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 rounded-xl shadow-2xl overflow-hidden border border-slate-700">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-2">Atualização de Banco de Dados</h2>
            <p class="text-slate-400 text-sm mb-6">Esta ferramenta irá criar a tabela <code>service_type_blocks</code> e importar os dados dos serviços 11 a 23.</p>

            <?php if ($msg): ?>
                <div class="mb-4 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg text-sm">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-lg text-sm">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="bg-slate-950 p-4 rounded-lg mb-6 font-mono text-xs text-slate-500 overflow-y-auto max-h-48">
                Arquivos a processar:<br>
                - setup_service_part1.sql<br>
                - setup_service_part2.sql<br>
                - setup_service_part3.sql
            </div>

            <form method="POST">
                <button type="submit" name="execute" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-500 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                    Executar Atualização
                </button>
            </form>
        </div>
        <div class="bg-slate-950/50 p-4 text-center text-xs text-slate-500 border-t border-slate-700">
            Remova este arquivo após o uso por segurança.
        </div>
    </div>
</body>
</html>
