<?php
// Nome do Arquivo: meus_clientes.php
// Função: Lista de Clientes com MENU UNIVERSAL.

require_once 'session_validator.php';
require_once 'config.php';
require_once 'db.php';

$id_usuario = $_SESSION['usuario_id'];
$ambiente_atual = $_SESSION['ambiente'] ?? 'indefinido';
$is_demo = ($ambiente_atual === 'demo');
$modo_suporte = isset($_SESSION['admin_original_id']);
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiro_nome = explode(' ', trim($nome_usuario))[0];

$conn = $is_demo ? Database::getDemo() : Database::getProd();

$clientes = [];
try {
    $sql = "SELECT * FROM Clientes WHERE id_criador = ? ORDER BY id_cliente DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $clientes[] = $row;
} catch (Exception $e) { }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes | SGT</title>
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes | SGT</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- QR Code Library (Client Side) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Exo 2', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            dark: '#0a0f1a',      // Was #001e3c -> SGT Background
                            primary: '#111827',   // Was #0a2e5c -> SGT Surface
                            surface: 'rgba(255,255,255,0.03)', // Was #132f4c -> SGT Glass Ultra Light
                            accent: '#f97316',    // Was #FF7518 -> SGT Orange
                            action: '#ea580c',    // Was #EA580C -> SGT Orange Dark
                            glow: '#3b82f6',      // Was #4fc3f7 -> SGT Blue
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Glassmorphism - Remapped to SGT Visuals */
        .glass-panel {
            background: rgba(17, 24, 39, 0.7); /* SGT Glass Base */
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Background - Remapped to SGT Dark */
        body {
            background-color: #0a0f1a;
            color: #f8fafc;
            /* Optional: Subtle grid pattern if desired, but sticking to pure CSS properties */
            background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            min-height: 100vh;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0a0f1a; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #f97316; }
    </style>
</head>
<body class="text-slate-200 font-sans antialiased selection:bg-brand-accent selection:text-brand-dark">

    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Page -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-accent/10 flex items-center justify-center text-brand-accent border border-brand-accent/20">
                    <i class="ph ph-address-book text-2xl"></i>
                </div>
                <div>
                    <h1 class="font-display text-2xl font-bold text-white">Carteira de Clientes</h1>
                    <p class="text-sm text-slate-400">Total: <strong class="text-white"><?php echo count($clientes); ?></strong> cadastrados</p>
                </div>
            </div>
            <a href="form_cliente.php" class="px-6 py-3 bg-brand-action hover:bg-brand-accent text-white font-bold rounded-xl shadow-lg shadow-orange-900/20 transition-all hover:scale-105 flex items-center gap-2">
                <i class="ph ph-user-plus text-xl"></i> Novo Cliente
            </a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'sucesso'): ?>
            <div class="glass-panel rounded-xl p-4 mb-6 border-l-4 border-green-500 flex items-center gap-3">
                <i class="ph ph-check-circle text-green-400 text-xl"></i>
                <span class="text-green-100 font-medium">Operação realizada com sucesso!</span>
            </div>
        <?php endif; ?>

        <!-- Tabela de Clientes -->
        <div class="glass-panel rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-black/20 text-xs text-slate-400 uppercase border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Nome</th>
                            <th class="px-6 py-4 font-semibold">Empresa</th>
                            <th class="px-6 py-4 font-semibold">Contatos</th>
                            <th class="px-6 py-4 font-semibold text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <i class="ph ph-users-three text-4xl mb-3 block opacity-30"></i>
                                    Nenhum cliente encontrado. Cadastre o primeiro!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $c): $inicial = !empty($c['nome_cliente']) ? strtoupper(substr($c['nome_cliente'], 0, 1)) : '?'; ?>
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-brand-surface border border-white/10 flex items-center justify-center text-brand-accent font-bold text-lg shadow-inner">
                                            <?php echo $inicial; ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-white group-hover:text-brand-accent transition-colors"><?php echo htmlspecialchars($c['nome_cliente']); ?></div>
                                            <div class="text-xs text-slate-500"><?php echo htmlspecialchars($c['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-300"><?php echo htmlspecialchars($c['empresa'] ?? '-'); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($c['cnpj_cpf']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($c['celular']): ?>
                                        <div class="flex items-center gap-2 text-slate-300 text-xs">
                                            <i class="ph ph-whatsapp-logo text-green-400 text-lg"></i>
                                            <?php echo $c['celular']; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-600">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="form_cliente.php?id=<?php echo $c['id_cliente']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/5 hover:bg-brand-accent hover:text-white text-slate-400 transition-all" title="Editar">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Share SGT -->
    <div id="modalShare" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeShareModal()"></div>
        
        <!-- Modal Content -->
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-4">
            <div class="glass-panel rounded-2xl border border-white/10 shadow-2xl overflow-hidden relative animate-float">
                
                <!-- Header -->
                <div class="bg-brand-primary p-4 flex justify-between items-center border-b border-white/10">
                    <h5 class="font-bold text-white flex items-center gap-2">
                        <i class="ph ph-share-network text-brand-accent"></i> Acesso do Cliente
                    </h5>
                    <button onclick="closeShareModal()" class="text-slate-400 hover:text-white transition-colors">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 flex flex-col items-center text-center">
                    <p class="text-xs text-slate-400 mb-4">Escaneie para acessar a área exclusiva de:</p>
                    <h3 id="shareClientName" class="text-lg font-bold text-white mb-6">Nome do Cliente</h3>
                    
                    <!-- QR Code Area -->
                    <div class="p-3 bg-white rounded-xl mb-6 shadow-lg shadow-white/5">
                        <div id="qrcode"></div>
                    </div>

                    <!-- Actions -->
                    <div class="w-full space-y-3">
                        <button id="btnWhatsapp" class="w-full py-3 bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl transition-all shadow-lg flex items-center justify-center gap-2 hover:scale-[1.02]">
                            <i class="ph ph-whatsapp-logo text-xl"></i> Enviar no WhatsApp
                        </button>
                        
                        <button id="btnCopy" class="w-full py-3 bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 font-medium rounded-xl transition-colors flex items-center justify-center gap-2">
                            <i class="ph ph-copy"></i> Copiar Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Logic -->
    <script>
        // URL de Produção solicitada
        // Aponta para a Home. Futuramente o index.php pode ler o ?id= para redirecionar se necessário.
        const baseUrl = 'https://elmtopografia.com.br/Orcamento/index.php?id=';

        function openShareModal(id, nome, celular) {
            const modal = document.getElementById('modalShare');
            const nameEl = document.getElementById('shareClientName');
            const qrEl = document.getElementById('qrcode');
            const btnZap = document.getElementById('btnWhatsapp');
            const btnCopy = document.getElementById('btnCopy');

            // 1. Setup Data
            const link = baseUrl + id;
            const zapLink = `https://wa.me/55${celular.replace(/\D/g,'')}?text=${encodeURIComponent('Olá ' + nome + ', acesse seu painel SGT aqui: ' + link)}`;

            // 2. Update UI
            nameEl.textContent = nome;
            
            // 3. Generate QR
            qrEl.innerHTML = ''; // Clear previous
            new QRCode(qrEl, {
                text: link,
                width: 160,
                height: 160,
                colorDark : "#0a0f1a",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });

            // 4. Setup Buttons
            btnZap.onclick = () => window.open(zapLink, '_blank');
            btnCopy.onclick = () => {
                navigator.clipboard.writeText(link);
                btnCopy.innerHTML = '<i class="ph ph-check text-green-400"></i> Copiado!';
                setTimeout(() => btnCopy.innerHTML = '<i class="ph ph-copy"></i> Copiar Link', 2000);
            };

            // 5. Show
            modal.classList.remove('hidden');
        }

        function closeShareModal() {
            document.getElementById('modalShare').classList.add('hidden');
        }
    </script>