<?php
// ARQUIVO: gerador_prompt_veo.php
require_once 'config.php';
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Identifica o usuário
$id_usuario = $_SESSION['usuario_id'] ?? $_SESSION['id_criador'] ?? 1;
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// Lógica de Menu
$is_demo = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGT | Gerador de Prompts Veo Pro</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
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
                        brand: {
                            dark: '#001e3c',
                            primary: '#0a2e5c',
                            surface: '#132f4c',
                            accent: '#FF7518',
                            action: '#EA580C',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Glassmorphism */
        .glass-panel {
            background: rgba(10, 46, 92, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        }
        body {
            background: radial-gradient(circle at center, #0a2e5c 0%, #001224 100%);
            min-height: 100vh;
        }
        select, input, textarea {
            background-color: rgba(0,0,0,0.2) !important;
            border-color: rgba(255,255,255,0.1) !important;
            color: #cbd5e1 !important;
        }
        select option {
            background-color: #0a2e5c;
            color: white;
        }
        /* Custom Scrollbar for form */
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
    </style>
</head>
<body class="text-slate-200 font-sans antialiased">
    <!-- Navbar -->
    <nav class="w-full glass-panel sticky top-0 z-50 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-4">
                    <img src="<?= BASE_URL ?>/assets/img/logo_sgt.png" alt="SGT" class="h-10">
                </div>
                <div class="hidden md:flex items-center gap-4">
                     <a href="painel.php" class="text-sm font-medium text-slate-300 hover:text-white transition-colors flex items-center gap-2">
                        <i class="ph ph-house"></i> Painel
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="glass-panel rounded-2xl p-6 mb-8 flex items-center gap-4 bg-gradient-to-r from-brand-surface to-brand-primary">
            <div class="w-14 h-14 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-300 border border-purple-500/30 shadow-lg shadow-purple-900/20">
                <i class="ph ph-magic-wand text-3xl"></i>
            </div>
            <div>
                <h1 class="font-display text-2xl font-bold text-white">Veo Prompt Studio Pro</h1>
                <p class="text-sm text-slate-400">Crie cenas cinematográficas detalhadas com IA</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Formulário (2 Colunas) -->
            <div class="lg:col-span-2 space-y-6">
                <form id="formVeo" onsubmit="event.preventDefault(); gerarPrompt();">
                    
                    <!-- BLOCO 1: AMBIENTE -->
                    <div class="glass-panel rounded-2xl p-6 mb-6 border-l-4 border-l-brand-accent">
                        <h3 class="font-display text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-brand-accent/20 text-brand-accent w-6 h-6 rounded flex items-center justify-center text-xs">1</span>
                            Ambiente
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="label-input">Onde acontece? (Em Inglês) <span class="text-red-400">*</span></label>
                                <input type="text" id="b1_local" class="input-field w-full rounded-lg p-2.5" placeholder="Ex: Modern Office, Busy Street, Forest...">
                            </div>
                            <div>
                                <label class="label-input">Tipo</label>
                                <select id="b1_tipo" class="input-field w-full rounded-lg p-2.5">
                                    <option value="Indoor scene">Interno (Indoor)</option>
                                    <option value="Outdoor scene">Externo (Outdoor)</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-input">Estilo Visual</label>
                                <select id="b1_estilo" class="input-field w-full rounded-lg p-2.5">
                                    <option value="Realistic">Realista</option>
                                    <option value="Cinematic">Cinematográfico</option>
                                    <option value="Cyberpunk">Cyberpunk / Futurista</option>
                                    <option value="Minimalist">Minimalista</option>
                                    <option value="Luxurious">Luxuoso/Premium</option>
                                    <option value="Chaotic">Caótico/Destruído</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-input">Texturas Predominantes</label>
                                <select id="b1_textura" class="input-field w-full rounded-lg p-2.5">
                                    <option value="">-- Selecione --</option>
                                    <option value="Concrete and Glass">Concreto e Vidro</option>
                                    <option value="Wood and warm tones">Madeira e Aconchego</option>
                                    <option value="Metal and Neon">Metal e Neon</option>
                                    <option value="Nature and Water">Natureza e Água</option>
                                    <option value="Dust and Smoke">Poeira e Fumaça</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-input">Paleta de Cores</label>
                                <select id="b1_cores" class="input-field w-full rounded-lg p-2.5">
                                    <option value="Natural coloring">Natural</option>
                                    <option value="Warm tones">Tons Quentes (Laranja/Vermelho)</option>
                                    <option value="Cold tones">Tons Frios (Azul/Ciano)</option>
                                    <option value="Black and Gold">Preto e Dourado</option>
                                    <option value="Vibrant Neon">Neon Vibrante</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- BLOCO 2: PERSONAGEM -->
                    <div class="glass-panel rounded-2xl p-6 mb-6 border-l-4 border-l-blue-500">
                        <h3 class="font-display text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-blue-500/20 text-blue-400 w-6 h-6 rounded flex items-center justify-center text-xs">2</span>
                            Personagem
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-input">Quantidade</label>
                                <select id="b2_qtd" class="input-field w-full rounded-lg p-2.5">
                                    <option value="A single">1 Pessoa</option>
                                    <option value="Two">2 Pessoas</option>
                                    <option value="A group of">Grupo</option>
                                    <option value="A crowd of">Multidão</option>
                                    <option value="No people, only">Ninguém (Apenas objetos/cenário)</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-input">Quem é? (Em Inglês) <span class="text-red-400">*</span></label>
                                <input type="text" id="b2_quem" class="input-field w-full rounded-lg p-2.5" placeholder="Ex: Topographer, Architect, Man in suit...">
                            </div>
                            <div class="md:col-span-2">
                                <label class="label-input">Aparência Física (Em Inglês - Opcional)</label>
                                <input type="text" id="b2_aparencia" class="input-field w-full rounded-lg p-2.5" placeholder="Ex: with beard and glasses, short hair...">
                            </div>
                            <div>
                                <label class="label-input">Roupa/Estilo</label>
                                <select id="b2_roupa" class="input-field w-full rounded-lg p-2.5">
                                    <option value="Casual clothing">Casual</option>
                                    <option value="Business suit">Traje Social/Executivo</option>
                                    <option value="Construction safety gear">Uniforme de Obra/EPI</option>
                                    <option value="Futuristic outfit">Futurista</option>
                                    <option value="Elegant Evening wear">Elegante/Gala</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-input">Expressão</label>
                                <select id="b2_expressao" class="input-field w-full rounded-lg p-2.5">
                                    <option value="Focused expression">Focado/Sério</option>
                                    <option value="Happy and smiling">Feliz/Sorrindo</option>
                                    <option value="Confident look">Confiante</option>
                                    <option value="Mysterious">Misterioso</option>
                                    <option value="Intense">Intenso</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- BLOCO 3: AÇÃO -->
                    <div class="glass-panel rounded-2xl p-6 mb-6 border-l-4 border-l-green-500">
                        <h3 class="font-display text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-green-500/20 text-green-400 w-6 h-6 rounded flex items-center justify-center text-xs">3</span>
                            Ação (8 Segundos)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="label-input">O que acontece? (Em Inglês) <span class="text-red-400">*</span></label>
                                <textarea id="b3_acao" rows="2" class="input-field w-full rounded-lg p-2.5" placeholder="Ex: adjusting equipment while looking at the horizon..."></textarea>
                            </div>
                            <div>
                                <label class="label-input">Ritmo</label>
                                <select id="b3_ritmo" class="input-field w-full rounded-lg p-2.5">
                                    <option value="Real-time motion">Tempo Real (Normal)</option>
                                    <option value="Slow motion">Câmera Lenta (Dramático)</option>
                                    <option value="Fast paced">Rápido/Agitado</option>
                                    <option value="Time-lapse">Time-lapse</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-input">Ação Marcante (Em Inglês - Opcional)</label>
                                <input type="text" id="b3_detalhe" class="input-field w-full rounded-lg p-2.5" placeholder="Ex: turns quickly to the camera, explosion in background...">
                            </div>
                        </div>
                    </div>

                    <!-- BLOCO 4: TÉCNICA -->
                    <div class="glass-panel rounded-2xl p-6 mb-6 border-l-4 border-l-yellow-500">
                        <h3 class="font-display text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="bg-yellow-500/20 text-yellow-400 w-6 h-6 rounded flex items-center justify-center text-xs">4</span>
                            Técnica Cinematográfica
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="label-input">Enquadramento</label>
                                <select id="b4_enquadramento" class="input-field w-full rounded-lg p-2.5">
                                    <option value="Medium Shot" selected>Médio (Cintura pra cima)</option>
                                    <option value="Extreme Close-Up">Macro (Detalhe Olho/Objeto)</option>
                                    <option value="Close-Up">Close (Rosto)</option>
                                    <option value="Wide Shot">Plano Aberto (Cenário)</option>
                                    <option value="Aerial View">Aérea (Drone)</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-input">Movimento Câmera</label>
                                <select id="b4_movimento" class="input-field w-full rounded-lg p-2.5">
                                    <option value="Static Shot">Estática (Parada)</option>
                                    <option value="Slow Dolly In">Dolly In (Aproximando)</option>
                                    <option value="Slow Dolly Out">Dolly Out (Afastando)</option>
                                    <option value="Pan Right">Pan (Girando)</option>
                                    <option value="Handheld Camera">Câmera na Mão (Tremida)</option>
                                    <option value="Smooth Gimbal Tracking">Gimbal (Suave seguindo)</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-input">Iluminação</label>
                                <select id="b4_luz" class="input-field w-full rounded-lg p-2.5">
                                    <option value="Cinematic Lighting" selected>Cinematográfica</option>
                                    <option value="Golden Hour Lighting">Golden Hour (Pôr do Sol)</option>
                                    <option value="Soft Diffused Lighting">Suave/Difusa (Nublado)</option>
                                    <option value="Hard Sunlight">Sol Forte/Duro</option>
                                    <option value="Neon Volumetric Lighting">Neon/Cyberpunk</option>
                                    <option value="Dramatic Backlight">Contra-luz/Silhueta</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-input">Som (Opcional)</label>
                                <select id="b4_som" class="input-field w-full rounded-lg p-2.5">
                                    <option value="">-- Ignorar --</option>
                                    <option value="Nature sounds">Natureza</option>
                                    <option value="City noise">Cidade</option>
                                    <option value="Silence">Silêncio</option>
                                </select>
                            </div>
                            <!-- Aspect Ratio -->
                            <div class="md:col-span-2 mt-2 pt-4 border-t border-white/10">
                                <label class="label-input mb-3">Formato do Vídeo (Aspect Ratio)</label>
                                <div class="flex gap-4">
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="aspect_ratio" value="--ar 16:9" checked class="peer sr-only">
                                        <div class="glass-panel p-3 rounded-xl border border-white/10 peer-checked:border-brand-accent peer-checked:bg-brand-accent/10 transition-all text-center">
                                            <i class="ph ph-monitor text-2xl mb-1 block"></i>
                                            <span class="text-xs font-bold">Horizontal (16:9)</span>
                                        </div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="aspect_ratio" value="--ar 9:16" class="peer sr-only">
                                        <div class="glass-panel p-3 rounded-xl border border-white/10 peer-checked:border-brand-accent peer-checked:bg-brand-accent/10 transition-all text-center">
                                            <i class="ph ph-device-mobile text-2xl mb-1 block"></i>
                                            <span class="text-xs font-bold">Vertical (9:16)</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-purple-500/20 flex items-center justify-center gap-2 transform hover:-translate-y-1">
                        <i class="ph ph-sparkle text-xl"></i> GERAR PROMPT VEO
                    </button>
                    
                    <div class="text-center mt-4 text-xs text-slate-500">
                        Por favor, preencha os campos dos passos 1 a 4 **em Inglês** para garantir a melhor geração do vídeo.<br>
                        O campo de Fala (5) pode manter o texto na língua desejada (Português, etc).
                    </div>
                </form>
            </div>

            <!-- Coluna Lateral: Resultado -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Resultado JSON -->
                <div class="glass-panel rounded-2xl p-6 flex flex-col h-[600px] border-l-4 border-l-purple-500">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-display text-lg font-bold text-white flex items-center gap-2">
                            <i class="ph ph-code"></i> Prompt JSON
                        </h3>
                        <span class="text-[10px] text-brand-accent bg-brand-accent/10 px-2 py-1 rounded border border-brand-accent/20">EDITÁVEL</span>
                    </div>
                    <div class="relative flex-1 group">
                        <textarea id="resultado_json" class="w-full h-full bg-black/40 border border-white/10 rounded-xl p-4 text-green-400 font-mono text-xs leading-relaxed outline-none resize-none custom-scroll focus:border-brand-accent transition-colors" placeholder="O JSON gerado aparecerá aqui..."></textarea>
                        <button onclick="copiar('resultado_json')" class="absolute top-2 right-2 p-2 bg-white/10 hover:bg-white/20 rounded-lg text-white transition-colors opacity-0 group-hover:opacity-100" title="Copiar JSON">
                            <i class="ph ph-copy"></i>
                        </button>
                    </div>
                    <div class="mt-2 text-[10px] text-slate-500 text-center">
                        Este formato é compatível com o sistema de automação de vídeos.
                    </div>
                </div>

                <!-- BLOCO 5: FALA (Extra) -->
                <div class="glass-panel rounded-2xl p-6 border-l-4 border-l-pink-500">
                    <h3 class="font-display text-lg font-bold text-white mb-4 flex items-center gap-2">
                         <span class="bg-pink-500/20 text-pink-400 w-6 h-6 rounded flex items-center justify-center text-xs">5</span>
                        Fala / Áudio (Opcional)
                    </h3>
                    <div class="space-y-3">
                         <div>
                            <label class="label-input">Texto da Fala</label>
                            <textarea id="b5_texto" rows="3" class="input-field w-full rounded-lg p-2.5" placeholder="Escreva o que o personagem diz..."></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <select id="b5_voz" class="input-field w-full rounded-lg p-2">
                                <option value="Male Voice">Voz Masculina</option>
                                <option value="Female Voice">Voz Feminina</option>
                            </select>
                            <select id="b5_tom" class="input-field w-full rounded-lg p-2">
                                <option value="Neutral tone">Neutro</option>
                                <option value="Inspiring">Inspirador</option>
                                <option value="Serious">Sério</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function gerarPrompt() {
            // BLOCO 1 - AMBIENTE
            const b1_local = document.getElementById('b1_local').value.trim() || "Modern location";
            const b1_tipo = document.getElementById('b1_tipo').value;
            const b1_estilo = document.getElementById('b1_estilo').value;
            const b1_textura = document.getElementById('b1_textura').value;
            const b1_cores = document.getElementById('b1_cores').value;

            // BLOCO 2 - PERSONAGEM
            const b2_qtd = document.getElementById('b2_qtd').value;
            const b2_quem = document.getElementById('b2_quem').value.trim() || "character";
            const b2_aparencia = document.getElementById('b2_aparencia').value.trim();
            const b2_roupa = document.getElementById('b2_roupa').value;
            const b2_expressao = document.getElementById('b2_expressao').value;
            
            // Construção do Character String para description
            let charString = `${b2_qtd} ${b2_quem}`;
            if(b2_aparencia) charString += `, ${b2_aparencia}`;
            charString += `, wearing ${b2_roupa}`;

            // BLOCO 3 - AÇÃO
            const b3_acao = document.getElementById('b3_acao').value.trim() || "acting naturally";
            const b3_ritmo = document.getElementById('b3_ritmo').value;
            const b3_detalhe = document.getElementById('b3_detalhe').value.trim();

            // BLOCO 4 - TÉCNICA
            const b4_enquadramento = document.getElementById('b4_enquadramento').value;
            const b4_movimento = document.getElementById('b4_movimento').value;
            const b4_luz = document.getElementById('b4_luz').value;
            const b4_som = document.getElementById('b4_som').value;
            const aspectRatio = document.querySelector('input[name="aspect_ratio"]:checked').value; // --ar 16:9

            // BLOCO 5 - FALA
            const b5_texto = document.getElementById('b5_texto').value.trim();
            const b5_voz = document.getElementById('b5_voz').value;
            const b5_tom = document.getElementById('b5_tom').value;

            // MONTAGEM DO JSON
            const promptObj = {
                "description": `${b4_enquadramento}, ${charString} ${b3_acao} in ${b1_local}. ${b1_estilo} style.`,
                "style": `${b1_estilo}, ${b1_cores}, photorealistic, 8k, cinematic masterpiece`,
                "camera": `${b4_movimento}, ${b4_enquadramento}, ${aspectRatio.replace('--ar ', 'Aspect Ratio ')}`,
                "lighting": b4_luz,
                "environment": `${b1_local}, ${b1_tipo}`,
                "elements": [
                    b2_quem,
                    b1_local,
                    b2_roupa
                ],
                "motion": `${b3_ritmo}, ${b3_acao}` + (b3_detalhe ? `, ${b3_detalhe}` : ""),
                "notes": `${aspectRatio}`,
                "voice": b5_texto ? {
                    "text": b5_texto,
                    "gender": b5_voz,
                    "tone": b5_tom,
                    "language": "pt-BR"
                } : null
            };

            // Adicionar textura se houver
            if(b1_textura) promptObj.environment += `, textures of ${b1_textura}`;
            
            // Adicionar Som se houver (fora do voice)
            if(b4_som) promptObj.audio_environment = b4_som;

            // Formatar JSON com indentação de 2 espaços
            const jsonOutput = JSON.stringify([promptObj], null, 2);

            document.getElementById('resultado_json').value = jsonOutput;
            
            // Auto Copy
            copiar('resultado_json');
        }

        function copiar(id) {
            const el = document.getElementById(id);
            if(!el.value) return;
            
            el.select();
            el.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(el.value).then(() => {
                // Feedback visual rápido
                const originalBg = el.style.backgroundColor;
                el.style.backgroundColor = 'rgba(34, 197, 94, 0.1)'; // Green tint
                const originalBorder = el.style.borderColor;
                el.style.borderColor = '#4ade80';
                
                setTimeout(() => {
                    el.style.backgroundColor = '';
                    el.style.borderColor = '';
                }, 300);
            });
        }
    </script>
    <style>
        .label-input {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
    </style>
</body>
</html>
