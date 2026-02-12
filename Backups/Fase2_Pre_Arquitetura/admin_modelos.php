<?php
require_once 'db.php';

// Controle de Ambiente (Produção vs Demo)
$env = isset($_GET['env']) && $_GET['env'] === 'demo' ? 'demo' : 'prod';
try {
    if ($env === 'demo') {
        $conn = Database::getDemo();
        $envLabel = "AMBIENTE DEMO";
        $envClass = "bg-orange-600";
    } else {
        $conn = Database::getProd();
        $envLabel = "AMBIENTE PRODUÇÃO";
        $envClass = "bg-blue-600";
    }
} catch (Exception $e) {
    die("Erro ao conectar no banco de dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Modelos de Proposta</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .card {
            transition: all 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="min-h-screen pb-12">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="painel.php" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-slate-800">Editor de Textos Padrão</h1>
                <span class="px-2 py-1 text-xs font-bold text-white rounded <?php echo $envClass; ?>"><?php echo $envLabel; ?></span>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500">Alternar Ambiente:</span>
                <a href="?env=prod" class="px-3 py-1 text-sm rounded-md <?php echo $env === 'prod' ? 'bg-blue-100 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-100'; ?>">Produção</a>
                <a href="?env=demo" class="px-3 py-1 text-sm rounded-md <?php echo $env === 'demo' ? 'bg-orange-100 text-orange-700 font-bold' : 'text-slate-600 hover:bg-slate-100'; ?>">Demo</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">

        <?php if (isset($_GET['msg'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // Busca blocos ordenados
            $sql = "SELECT * FROM proposal_block_templates ORDER BY `order` ASC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    // Mapeia categorias para cores
                    $colors = [
                        'presentation' => 'border-l-purple-500',
                        'technical' => 'border-l-sky-500',
                        'financial' => 'border-l-emerald-500',
                        'legal' => 'border-l-slate-400',
                        'layout' => 'border-l-gray-800'
                    ];
                    $borderClass = $colors[$row['category']] ?? 'border-l-gray-300';
            ?>
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 <?php echo $borderClass; ?> card flex flex-col h-full">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider"><?php echo $row['category']; ?></span>
                                <h3 class="text-lg font-bold text-slate-800 leading-tight mt-1"><?php echo htmlspecialchars($row['name']); ?></h3>
                            </div>
                            <span class="bg-slate-100 text-slate-500 text-xs px-2 py-1 rounded-full font-mono">#<?php echo $row['order']; ?></span>
                        </div>

                        <p class="text-sm text-slate-500 mb-6 flex-1">
                            Gerencia o texto padrão e as variáveis permitidas para o bloco <strong><?php echo htmlspecialchars($row['slug']); ?></strong>.
                        </p>

                        <div class="mt-auto pt-4 border-t border-slate-100">
                            <a href="admin_editar_modelo.php?slug=<?php echo $row['slug']; ?>&env=<?php echo $env; ?>"
                                class="flex items-center justify-center w-full px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition-colors gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar Conteúdo
                            </a>
                        </div>
                    </div>
                <?php
                endwhile;
            else:
                ?>
                <div class="col-span-full text-center py-12 text-slate-400">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>Nenhum modelo de bloco encontrado na base de dados.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>