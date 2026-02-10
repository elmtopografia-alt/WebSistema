<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | CRM 2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Configuração SGT -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        background: '#0a0f1a',
                        surface: '#111827',
                        primary: '#f97316',
                        glass: 'rgba(17, 24, 39, 0.7)'
                    }
                }
            }
        }
    </script>
    <style>
        body { background: #0a0f1a; color: #e2e8f0; }
        .glass { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
        .kanban-col { min-height: 500px; }
        .ghost-card { background: rgba(249, 115, 22, 0.1); border: 2px dashed #f97316; opacity: 0.5; }
        /* Mobile Hardening */
        @media (max-width: 768px) {
            .kanban-container { overflow-x: auto; scroll-snap-type: x mandatory; display: flex; gap: 1rem; padding-bottom: 2rem; }
            .kanban-col-wrapper { min-width: 85vw; scroll-snap-align: center; }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <!-- Navbar Simplificada -->
    <nav class="glass sticky top-0 z-50 border-b border-white/10 h-16 flex items-center justify-between px-4">
        <div class="flex items-center gap-4">
            <a href="painel.php" class="text-slate-400 hover:text-white transition-colors"><i class="ph ph-arrow-left text-xl"></i></a>
            <h1 class="font-bold text-lg"><span class="text-orange-500">SGT</span> CRM</h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500 hidden md:block">Arraste para mover</span>
            <button onclick="location.reload()" class="p-2 text-slate-400 hover:text-white"><i class="ph ph-arrows-clockwise text-xl"></i></button>
        </div>
    </nav>

    <!-- Kanban Board -->
    <main class="flex-1 p-4 md:p-6 overflow-hidden">
        <div class="kanban-container grid md:grid-cols-4 gap-4 h-full">

            <!-- Coluna: Elaboração -->
            <div class="kanban-col-wrapper flex flex-col h-full">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="font-bold text-slate-300 flex items-center gap-2"><i class="ph ph-pencil-simple text-slate-500"></i> Elaboração</span>
                    <span class="text-xs bg-white/5 px-2 py-0.5 rounded text-slate-500" id="count-elaboracao">0</span>
                </div>
                <div id="col-elaboracao" class="kanban-col bg-surface/50 rounded-xl p-3 space-y-3 flex-1 border border-white/5" data-fase="ELABORACAO">
                    <!-- Cards via PHP/AJAX -->
                </div>
            </div>

            <!-- Coluna: Enviadas -->
            <div class="kanban-col-wrapper flex flex-col h-full">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="font-bold text-blue-400 flex items-center gap-2"><i class="ph ph-paper-plane-tilt"></i> Enviadas</span>
                    <span class="text-xs bg-blue-500/10 px-2 py-0.5 rounded text-blue-400" id="count-enviada">0</span>
                </div>
                <div id="col-enviada" class="kanban-col bg-surface/50 rounded-xl p-3 space-y-3 flex-1 border border-blue-500/10" data-fase="ENVIADA"></div>
            </div>

            <!-- Coluna: Negociação -->
            <div class="kanban-col-wrapper flex flex-col h-full">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="font-bold text-amber-400 flex items-center gap-2"><i class="ph ph-handshake"></i> Negociação</span>
                    <span class="text-xs bg-amber-500/10 px-2 py-0.5 rounded text-amber-400" id="count-negociacao">0</span>
                </div>
                <div id="col-negociacao" class="kanban-col bg-surface/50 rounded-xl p-3 space-y-3 flex-1 border border-amber-500/10" data-fase="NEGOCIACAO"></div>
            </div>

            <!-- Coluna: Fechadas -->
            <div class="kanban-col-wrapper flex flex-col h-full">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="font-bold text-green-400 flex items-center gap-2"><i class="ph ph-check-circle"></i> Fechadas</span>
                    <span class="text-xs bg-green-500/10 px-2 py-0.5 rounded text-green-400" id="count-fechada">0</span>
                </div>
                <div id="col-fechada" class="kanban-col bg-surface/50 rounded-xl p-3 space-y-3 flex-1 border border-green-500/10" data-fase="FECHADA"></div>
            </div>

        </div>
    </main>

    <!-- Logic Script (Sortable + API) -->
    <script>
        // Lógica de Renderização e Drag & Drop aqui (será injetada pelo Backend)
    </script>
</body>
</html>
