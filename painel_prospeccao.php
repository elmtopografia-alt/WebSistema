<?php
// painel_prospeccao.php
declare(strict_types=1);
require 'conexao.php';

// 1. Autenticação e Segurança (Hardened)
if (file_exists('session_validator.php')) {
    require_once 'session_validator.php';
} else {
    session_start();
}

// Bloqueio Total para Não-Admins
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'admin') {
    // Log de tentativa de acesso indevido (opcional)
    header("Location: painel.php?msg=acesso_negado");
    exit;
}

// 2. Validação de Inputs
$status = filter_input(INPUT_GET, 'status', FILTER_VALIDATE_REGEXP, [
    'options' => ['regexp' => '/^(PENDENTE|ENVIADO|FALHA|IGNORADO)$/']
]) ?: 'PENDENTE';

$ordem = filter_input(INPUT_GET, 'ordem', FILTER_VALIDATE_REGEXP, [
    'options' => ['regexp' => '/^(id|nome|data)_(asc|desc)$/']
]) ?: 'id_desc';

// 3. Montagem da Query
$sqlBase = "SELECT * FROM leads_prospeccao WHERE status_envio = ?";
$orderSql = 'id DESC';
switch ($ordem) {
    case 'nome_asc': $orderSql = 'nome_empresa ASC'; break;
    case 'nome_desc': $orderSql = 'nome_empresa DESC'; break;
    case 'data_asc': $orderSql = 'data_captura ASC'; break;
}

// 4. Exportação CSV (Antes de qualquer HTML)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sgt_leads_' . date('Y-m-d_Hi') . '.csv');
    
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Empresa', 'Site', 'Ramo', 'WhatsApp', 'Email', 'Captura', 'Status', 'Data']); // Cabeçalho
    
    $stmt = $pdo->prepare($sqlBase . " ORDER BY " . $orderSql);
    $stmt->execute([$status]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $row['id'],
            $row['nome_empresa'],
            $row['site_origem'],
            $row['ramo_atuacao'],
            $row['whatsapp'],
            $row['email_contato'],
            $row['metodo_captura'],
            $row['status_envio'],
            $row['data_captura']
        ]);
    }
    fclose($out);
    exit;
}

// 5. Query Visualização (Com Limite)
$stmt = $pdo->prepare($sqlBase . " ORDER BY " . $orderSql . " LIMIT 50");
$stmt->execute([$status]);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// KPIs
$kpi = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(metodo_captura = 'wa_link') as wpp,
        SUM(metodo_captura = 'public_form') as forms
    FROM leads_prospeccao
")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | CRM de Prospecção</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Exo 2', 'sans-serif'],
                    },
                    colors: {
                        background: '#0a0f1a',
                        surface: '#111827',
                        primary: '#f97316', 
                        secondary: '#3b82f6',
                    },
                    animation: {
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                    },
                    keyframes: {
                        'pulse-glow': {
                            '0%, 100%': { boxShadow: '0 0 20px rgba(249, 115, 22, 0.4)' },
                            '50%': { boxShadow: '0 0 40px rgba(249, 115, 22, 0.6)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { background-color: #0a0f1a; color: #f8fafc; }
        
        /* Glass Effect Premium */
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(249, 115, 22, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
        }

        .grid-bg {
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0a0f1a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="antialiased min-h-screen">

    <!-- Background Ambience -->
    <div class="fixed inset-0 grid-bg opacity-30 pointer-events-none z-0"></div>
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="fixed bottom-1/4 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-[100px] pointer-events-none z-0"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <a href="painel.php" class="inline-flex items-center text-slate-400 hover:text-white mb-2 transition-colors gap-1 text-sm">
                    <i class="ph ph-arrow-left"></i> Voltar ao Painel
                </a>
                <h1 class="font-display text-3xl md:text-4xl font-bold text-white mb-1">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-orange-600">CRM</span> de Prospecção
                </h1>
                <p class="text-slate-400 max-w-2xl">Gerencie seus leads capturados via Site e WhatsApp em tempo real.</p>
            </div>
            
            <div class="flex gap-3">
                <a href="?status=<?= $status ?>&ordem=<?= $ordem ?>&export=csv" class="glass px-4 py-2 rounded-xl text-green-400 font-bold hover:bg-green-500/10 hover:border-green-500/50 transition-all flex items-center gap-2">
                    <i class="ph ph-microsoft-excel-logo text-lg"></i>
                    Exportar Planilha
                </a>
            </div>
        </div>

        <!-- KPIs Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass-card p-6 rounded-2xl relative overflow-hidden group">
                <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="ph ph-users-three text-6xl text-white"></i>
                </div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Total de Leads</p>
                <h3 class="text-4xl font-bold text-white font-display"><?= $kpi['total'] ?></h3>
                <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                    <span class="px-2 py-1 rounded bg-white/5 border border-white/10">Base Total</span>
                </div>
            </div>

            <div class="glass-card p-6 rounded-2xl relative overflow-hidden group border-green-500/20">
                <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="ph ph-whatsapp-logo text-6xl text-green-400"></i>
                </div>
                <p class="text-green-400 text-xs font-bold uppercase tracking-wider mb-1">Via WhatsApp</p>
                <h3 class="text-4xl font-bold text-white font-display"><?= $kpi['wpp'] ?></h3>
                <div class="mt-4 w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-green-500 h-full" style="width: <?= $kpi['total'] > 0 ? ($kpi['wpp']/$kpi['total'])*100 : 0 ?>%"></div>
                </div>
            </div>

            <div class="glass-card p-6 rounded-2xl relative overflow-hidden group border-blue-500/20">
                <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="ph ph-browser text-6xl text-blue-400"></i>
                </div>
                <p class="text-blue-400 text-xs font-bold uppercase tracking-wider mb-1">Via Site</p>
                <h3 class="text-4xl font-bold text-white font-display"><?= $kpi['forms'] ?></h3>
                <div class="mt-4 w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-500 h-full" style="width: <?= $kpi['total'] > 0 ? ($kpi['forms']/$kpi['total'])*100 : 0 ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Filters & Toolbar -->
        <div class="glass rounded-2xl p-4 mb-6 sticky top-4 z-40 flex flex-wrap gap-4 items-center justify-between">
            <form method="GET" class="flex flex-wrap gap-4 items-center w-full md:w-auto">
                <div class="relative group">
                    <i class="ph ph-funnel absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-hover:text-orange-400 transition-colors"></i>
                    <select name="status" onchange="this.form.submit()" class="pl-10 pr-8 py-2 bg-[#0a0f1a] border border-white/10 rounded-xl text-slate-300 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 appearance-none cursor-pointer hover:bg-white/5 transition-colors">
                        <?php foreach(['PENDENTE','ENVIADO','FALHA','IGNORADO'] as $s): ?>
                        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="relative group">
                    <i class="ph ph-sort-ascending absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-hover:text-blue-400 transition-colors"></i>
                    <select name="ordem" onchange="this.form.submit()" class="pl-10 pr-8 py-2 bg-[#0a0f1a] border border-white/10 rounded-xl text-slate-300 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 appearance-none cursor-pointer hover:bg-white/5 transition-colors">
                        <option value="id_desc" <?= $ordem==='id_desc'?'selected':'' ?>>Mais Recentes</option>
                        <option value="nome_asc" <?= $ordem==='nome_asc'?'selected':'' ?>>Nome (A-Z)</option>
                        <option value="data_asc" <?= $ordem==='data_asc'?'selected':'' ?>>Mais Antigos</option>
                    </select>
                </div>
            </form>
            
            <span class="text-xs text-slate-500 font-mono hidden md:inline-block">Exibindo últimos 50 registros</span>
        </div>

        <!-- Content Grid (Cards Layout instead of Table for Modern Feel) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach($leads as $l): 
                $badgeColor = match($l['status_envio']) {
                    'ENVIADO' => 'text-green-400 bg-green-500/10 border-green-500/20',
                    'FALHA' => 'text-red-400 bg-red-500/10 border-red-500/20',
                    'IGNORADO' => 'text-slate-400 bg-slate-500/10 border-slate-500/20',
                    default => 'text-yellow-500 bg-yellow-500/10 border-yellow-500/20'
                };
                
                $dataF = date('d/m H:i', strtotime($l['data_captura']));
            ?>
            <div class="glass-card rounded-xl p-5 group relative">
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-lg font-bold text-slate-300 border border-white/10">
                            <?= strtoupper(substr($l['nome_empresa'], 0, 1)) ?>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-lg leading-tight line-clamp-1" title="<?= $l['nome_empresa'] ?>">
                                <?= $l['nome_empresa'] ?>
                            </h4>
                            <p class="text-xs text-slate-500 flex items-center gap-1">
                                <i class="ph ph-clock"></i> <?= $dataF ?>
                            </p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-1 rounded-md border <?= $badgeColor ?>">
                        <?= $l['status_envio'] ?>
                    </span>
                </div>

                <!-- Info -->
                <div class="space-y-3 mb-6">
                    <div class="flex items-center gap-2 text-sm text-slate-300">
                        <i class="ph ph-buildings text-slate-500"></i>
                        <span class="truncate"><?= $l['ramo_atuacao'] ?: 'Não informado' ?></span>
                    </div>
                    <?php if($l['site_origem']): ?>
                    <a href="<?= $l['site_origem'] ?>" target="_blank" class="flex items-center gap-2 text-sm text-blue-400 hover:text-blue-300 transition-colors w-fit">
                        <i class="ph ph-link"></i>
                        <span class="truncate max-w-[200px]"><?= parse_url($l['site_origem'], PHP_URL_HOST) ?></span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 pt-4 border-t border-white/5">
                    <?php if($l['whatsapp']): ?>
                    <a href="https://wa.me/<?= $l['whatsapp'] ?>" target="_blank" class="flex-1 btn-shine bg-green-600 hover:bg-green-500 text-white text-sm font-bold py-2 rounded-lg flex items-center justify-center gap-2 transition-all hover:-translate-y-1 shadow-lg shadow-green-900/20">
                        <i class="ph ph-whatsapp-logo text-lg"></i>
                        Chamar
                    </a>
                    <?php else: ?>
                    <button disabled class="flex-1 bg-white/5 text-slate-500 text-sm font-bold py-2 rounded-lg flex items-center justify-center gap-2 cursor-not-allowed">
                        <i class="ph ph-whatsapp-logo text-lg"></i>
                        Sem Zap
                    </button>
                    <?php endif; ?>
                    
                    <button class="px-3 py-2 rounded-lg border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 transition-colors" title="Ver Detalhes">
                        <i class="ph ph-dots-three-vertical text-lg"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
    </div>

</body>
</html>
