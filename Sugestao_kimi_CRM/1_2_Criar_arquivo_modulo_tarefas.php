<?php
// modulo_tarefas.php - Widget de Tarefas para integrar no painel
require_once 'db.php';
require_once 'session_validator.php';

$id_usuario = $_SESSION['usuario_id'];
$conn = Database::getProd();

// Busca estatísticas para o widget
$stmt = $conn->prepare("
    SELECT 
        COUNT(CASE WHEN DATE(data_agendada) = CURDATE() AND status = 'pendente' THEN 1 END) as hoje,
        COUNT(CASE WHEN data_agendada < NOW() AND status = 'pendente' THEN 1 END) as atrasadas,
        COUNT(CASE WHEN data_agendada BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) AND status = 'pendente' THEN 1 END) as proximas
    FROM Tarefas_CRM 
    WHERE id_usuario = ?
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!-- Widget de Tarefas - Cole isso no seu painel_crm.php -->
<div id="widget-tarefas" class="fixed right-4 top-20 w-80 glass rounded-xl border border-white/10 shadow-2xl z-40 hidden md:block">
    <!-- Header -->
    <div class="p-4 border-b border-white/10 flex justify-between items-center bg-gradient-to-r from-orange-600/20 to-transparent">
        <div class="flex items-center gap-2">
            <i class="ph ph-check-square text-orange-500 text-xl"></i>
            <h3 class="font-bold text-white">Tarefas</h3>
        </div>
        <div class="flex gap-2">
            <button onclick="toggleTarefas()" class="text-slate-400 hover:text-white">
                <i class="ph ph-minus"></i>
            </button>
        </div>
    </div>

    <!-- Abas -->
    <div class="flex border-b border-white/5 text-xs">
        <button onclick="carregarTarefas('hoje')" class="flex-1 py-2 text-center hover:bg-white/5 text-orange-400 font-bold border-b-2 border-orange-500" id="tab-hoje">
            Hoje <?php if($stats['hoje'] > 0): ?><span class="ml-1 bg-orange-500 text-white px-1.5 rounded-full text-[10px]"><?= $stats['hoje'] ?></span><?php endif; ?>
        </button>
        <button onclick="carregarTarefas('atrasadas')" class="flex-1 py-2 text-center hover:bg-white/5 text-slate-400 border-b-2 border-transparent" id="tab-atrasadas">
            Atrasadas <?php if($stats['atrasadas'] > 0): ?><span class="ml-1 bg-red-500 text-white px-1.5 rounded-full text-[10px]"><?= $stats['atrasadas'] ?></span><?php endif; ?>
        </button>
        <button onclick="carregarTarefas('proximas')" class="flex-1 py-2 text-center hover:bg-white/5 text-slate-400 border-b-2 border-transparent" id="tab-proximas">
            +7d <?php if($stats['proximas'] > 0): ?><span class="ml-1 bg-blue-500 text-white px-1.5 rounded-full text-[10px]"><?= $stats['proximas'] ?></span><?php endif; ?>
        </button>
    </div>

    <!-- Lista -->
    <div id="lista-tarefas" class="max-h-96 overflow-y-auto p-2 space-y-2">
        <div class="text-center py-8 text-slate-500 text-sm">
            <i class="ph ph-spinner animate-spin text-2xl mb-2 block"></i>
            Carregando...
        </div>
    </div>

    <!-- Footer -->
    <div class="p-3 border-t border-white/10 bg-black/20">
        <button onclick="abrirModalNovaTarefa()" class="w-full py-2 bg-orange-600/20 hover:bg-orange-600/30 text-orange-400 rounded-lg text-sm font-bold flex items-center justify-center gap-2 transition-colors">
            <i class="ph ph-plus"></i> Nova Tarefa
        </button>
    </div>
</div>

<!-- Botão flutuante para mobile -->
<button onclick="toggleTarefas()" class="fixed bottom-4 right-4 w-14 h-14 bg-orange-600 rounded-full shadow-lg flex items-center justify-center text-white md:hidden z-50">
    <i class="ph ph-check-square text-2xl"></i>
    <?php if($stats['hoje'] + $stats['atrasadas'] > 0): ?>
    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-xs flex items-center justify-center font-bold">
        <?= $stats['hoje'] + $stats['atrasadas'] ?>
    </span>
    <?php endif; ?>
</button>

<!-- Modal Nova Tarefa -->
<div id="modal-tarefa" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-[#1F2937] rounded-2xl w-full max-w-md border border-white/10 shadow-2xl transform scale-95 opacity-0 transition-all" id="modal-tarefa-content">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">Nova Tarefa</h3>
                <button onclick="fecharModalTarefa()" class="text-slate-400 hover:text-white">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <form id="form-tarefa" onsubmit="salvarTarefa(event)">
                <input type="hidden" id="tarefa_id_proposta" name="id_proposta">
                
                <div class="space-y-4">
                    <!-- Cliente (selecionado ou busca) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Proposta/Cliente</label>
                        <select id="tarefa_select_proposta" class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none" onchange="document.getElementById('tarefa_id_proposta').value = this.value">
                            <option value="">Selecione uma proposta...</option>
                        </select>
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Tipo de Ação</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="tipo" value="ligacao" class="hidden peer" checked>
                                <div class="p-2 rounded-lg border border-white/10 bg-black/20 text-center peer-checked:bg-blue-600/20 peer-checked:border-blue-500 transition-all">
                                    <i class="ph ph-phone-call text-blue-400 text-xl mb-1 block"></i>
                                    <span class="text-xs text-slate-300">Ligação</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="tipo" value="whatsapp" class="hidden peer">
                                <div class="p-2 rounded-lg border border-white/10 bg-black/20 text-center peer-checked:bg-green-600/20 peer-checked:border-green-500 transition-all">
                                    <i class="ph ph-whatsapp-logo text-green-400 text-xl mb-1 block"></i>
                                    <span class="text-xs text-slate-300">WhatsApp</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="tipo" value="email" class="hidden peer">
                                <div class="p-2 rounded-lg border border-white/10 bg-black/20 text-center peer-checked:bg-purple-600/20 peer-checked:border-purple-500 transition-all">
                                    <i class="ph ph-envelope text-purple-400 text-xl mb-1 block"></i>
                                    <span class="text-xs text-slate-300">E-mail</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Data e Hora -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Data</label>
                            <input type="date" name="data_agendada" id="tarefa_data" required
                                   class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Hora</label>
                            <input type="time" name="hora_agendada" id="tarefa_hora" required
                                   class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none">
                        </div>
                    </div>

                    <!-- Prioridade -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Prioridade</label>
                        <select name="prioridade" class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none">
                            <option value="baixa">Baixa</option>
                            <option value="media" selected>Média</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>

                    <!-- Descrição -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Descrição/Observação</label>
                        <textarea name="descricao" rows="2" placeholder="Ex: Ligar para discutir valores..."
                                  class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none resize-none"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="fecharModalTarefa()" class="flex-1 py-2 bg-white/5 hover:bg-white/10 text-slate-300 rounded-lg font-bold transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-lg font-bold transition-colors flex items-center justify-center gap-2">
                        <i class="ph ph-check"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Estado global
let tarefasAberto = true;
let filtroAtual = 'hoje';

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    carregarTarefas('hoje');
    carregarPropostasSelect();
    
    // Atualiza a cada 5 minutos
    setInterval(() => carregarTarefas(filtroAtual), 300000);
});

function toggleTarefas() {
    const widget = document.getElementById('widget-tarefas');
    tarefasAberto = !tarefasAberto;
    widget.classList.toggle('hidden', !tarefasAberto);
}

function carregarTarefas(filtro) {
    filtroAtual = filtro;
    
    // Atualiza abas visuais
    document.querySelectorAll('[id^="tab-"]').forEach(tab => {
        tab.classList.remove('text-orange-400', 'border-orange-500', 'font-bold');
        tab.classList.add('text-slate-400', 'border-transparent');
    });
    document.getElementById('tab-' + filtro).classList.add('text-orange-400', 'border-orange-500', 'font-bold');
    document.getElementById('tab-' + filtro).classList.remove('text-slate-400', 'border-transparent');

    fetch('api/tarefas_api.php?acao=listar&filtro=' + filtro)
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('lista-tarefas');
            
            if (!data.sucesso || data.tarefas.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-slate-500">
                        <i class="ph ph-check-circle text-4xl mb-2 opacity-30"></i>
                        <p class="text-sm">Nenhuma tarefa ${filtro}</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = data.tarefas.map(t => `
                <div class="bg-white/5 rounded-lg p-3 border-l-2 border-${t.cor_urgencia}-500 hover:bg-white/10 transition-colors group">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-2">
                            <i class="ph ${t.icone} text-${t.cor_urgencia}-400"></i>
                            <span class="text-xs font-bold text-${t.cor_urgencia}-400 uppercase">${t.tipo}</span>
                        </div>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="completarTarefa(${t.id_tarefa})" class="p-1 hover:bg-green-500/20 text-green-400 rounded" title="Concluir">
                                <i class="ph ph-check"></i>
                            </button>
                            <button onclick="excluirTarefa(${t.id_tarefa})" class="p-1 hover:bg-red-500/20 text-red-400 rounded" title="Excluir">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <h4 class="font-bold text-white text-sm mb-1 truncate" title="${t.nome_cliente}">${t.nome_cliente}</h4>
                    
                    <div class="flex justify-between items-center text-xs text-slate-400">
                        <span class="flex items-center gap-1">
                            <i class="ph ph-clock"></i>
                            ${t.hora}
                        </span>
                        <span class="${t.dias_restantes < 0 ? 'text-red-400 font-bold' : ''}">
                            ${t.dias_restantes < 0 ? 'Atrasada ' + Math.abs(t.dias_restantes) + 'd' : 
                              t.dias_restantes === 0 ? 'Hoje' : 
                              'Em ' + t.dias_restantes + 'd'}
                        </span>
                    </div>
                    
                    ${t.descricao ? `<p class="text-xs text-slate-500 mt-2 line-clamp-2">${t.descricao}</p>` : ''}
                    
                    <div class="mt-2 flex gap-2">
                        <a href="https://wa.me/55${t.whatsapp.replace(/\D/g,'')}" target="_blank" 
                           class="text-xs bg-green-600/20 text-green-400 px-2 py-1 rounded hover:bg-green-600/30 flex items-center gap-1">
                            <i class="ph ph-whatsapp-logo"></i> Zap
                        </a>
                        <a href="gerar_proposta_html.php?id=${t.id_proposta}" target="_blank"
                           class="text-xs bg-blue-600/20 text-blue-400 px-2 py-1 rounded hover:bg-blue-600/30 flex items-center gap-1">
                            <i class="ph ph-eye"></i> Ver
                        </a>
                    </div>
                </div>
            `).join('');
        })
        .catch(err => {
            console.error('Erro ao carregar tarefas:', err);
        });
}

function abrirModalNovaTarefa(idProposta = null) {
    document.getElementById('modal-tarefa').classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('modal-tarefa-content').classList.remove('scale-95', 'opacity-0');
        document.getElementById('modal-tarefa-content').classList.add('scale-100', 'opacity-100');
    }, 10);
    
    // Preenche data padrão (amanhã)
    const amanha = new Date();
    amanha.setDate(amanha.getDate() + 1);
    document.getElementById('tarefa_data').value = amanha.toISOString().split('T')[0];
    document.getElementById('tarefa_hora').value = '09:00';
    
    if (idProposta) {
        document.getElementById('tarefa_id_proposta').value = idProposta;
        document.getElementById('tarefa_select_proposta').value = idProposta;
    }
}

function fecharModalTarefa() {
    document.getElementById('modal-tarefa-content').classList.remove('scale-100', 'opacity-100');
    document.getElementById('modal-tarefa-content').classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        document.getElementById('modal-tarefa').classList.add('hidden');
    }, 200);
}

function salvarTarefa(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const data = {
        acao: 'criar',
        id_proposta: formData.get('id_proposta'),
        tipo: formData.get('tipo'),
        data_agendada: formData.get('data_agendada') + ' ' + formData.get('hora_agendada'),
        prioridade: formData.get('prioridade'),
        descricao: formData.get('descricao')
    };

    fetch('api/tarefas_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if (res.sucesso) {
            fecharModalTarefa();
            form.reset();
            carregarTarefas(filtroAtual);
            
            // Notificação visual simples
            mostrarNotificacao('Tarefa criada: ' + res.cliente, 'success');
        } else {
            mostrarNotificacao(res.erro, 'error');
        }
    });
}

function completarTarefa(id) {
    const resultado = prompt('Resultado da tarefa?\n1: Concluída\n2: Não atendeu\n3: Agendado nova data\n4: Recusou', '1');
    if (!resultado) return;
    
    const mapa = { '1': 'concluida', '2': 'nao_atendeu', '3': 'agendado_nova_data', '4': 'recusou' };
    
    fetch('api/tarefas_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            acao: 'completar',
            id_tarefa: id,
            resultado: mapa[resultado] || 'concluida'
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.sucesso) {
            carregarTarefas(filtroAtual);
            mostrarNotificacao('Tarefa concluída!', 'success');
            
            if (res.sugestao_proxima) {
                if (confirm('Deseja criar uma nova tarefa de follow-up?')) {
                    abrirModalNovaTarefa();
                }
            }
        }
    });
}

function excluirTarefa(id) {
    if (!confirm('Tem certeza que deseja excluir esta tarefa?')) return;
    
    fetch('api/tarefas_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ acao: 'excluir', id_tarefa: id })
    })
    .then(r => r.json())
    .then(res => {
        if (res.sucesso) {
            carregarTarefas(filtroAtual);
        }
    });
}

function carregarPropostasSelect() {
    // Busca propostas ativas para preencher o select
    fetch('api/crm_controller.php?acao=listar_propostas_ativas')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('tarefa_select_proposta');
            if (data.propostas) {
                select.innerHTML = '<option value="">Selecione uma proposta...</option>' +
                    data.propostas.map(p => `
                        <option value="${p.id_proposta}">
                            #${p.id_proposta} - ${p.nome_cliente} (${p.status})
                        </option>
                    `).join('');
            }
        })
        .catch(() => {
            // Fallback silencioso - usuário pode digitar ID manualmente se necessário
        });
}

function mostrarNotificacao(msg, tipo) {
    // Implementação simples de toast notification
    const div = document.createElement('div');
    div.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-bold z-50 transform translate-x-full transition-transform duration-300 ${tipo === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
    div.textContent = msg;
    document.body.appendChild(div);
    
    setTimeout(() => div.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        div.classList.add('translate-x-full');
        setTimeout(() => div.remove(), 300);
    }, 3000);
}
</script>