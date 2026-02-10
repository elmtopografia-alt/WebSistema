<!-- Modal Timeline 360° -->
<div id="modal-timeline" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 py-8 flex justify-center">
        <div class="bg-[#111827] w-full max-w-4xl rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
            
            <!-- Header -->
            <div class="p-6 border-b border-white/10 bg-gradient-to-r from-blue-600/20 to-transparent flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-2xl font-bold text-white" id="timeline-titulo">Timeline</h2>
                        <span id="timeline-badge" class="px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                            #<span id="timeline-id"></span>
                        </span>
                    </div>
                    <p class="text-slate-400" id="timeline-subtitulo">Carregando...</p>
                </div>
                <button onclick="fecharTimeline()" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                    <i class="ph ph-x text-2xl text-slate-400"></i>
                </button>
            </div>

            <div class="grid md:grid-cols-3 gap-0">
                <!-- Sidebar com Info do Cliente/Proposta -->
                <div class="md:col-span-1 p-6 bg-black/20 border-r border-white/5">
                    <div id="timeline-info" class="space-y-4">
                        <!-- Preenchido via JS -->
                    </div>
                    
                    <!-- Ações Rápidas -->
                    <div class="mt-6 pt-6 border-t border-white/10 space-y-2">
                        <button onclick="abrirModalNovaTarefaFromTimeline()" class="w-full py-2 bg-orange-600/20 hover:bg-orange-600/30 text-orange-400 rounded-lg font-bold text-sm flex items-center justify-center gap-2 transition-colors">
                            <i class="ph ph-calendar-plus"></i> Agendar Tarefa
                        </button>
                        <button onclick="abrirNotaRapida()" class="w-full py-2 bg-white/5 hover:bg-white/10 text-slate-300 rounded-lg font-bold text-sm flex items-center justify-center gap-2 transition-colors">
                            <i class="ph ph-note"></i> Adicionar Nota
                        </button>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="md:col-span-2 p-6 max-h-[70vh] overflow-y-auto">
                    <div id="timeline-content" class="space-y-6">
                        <!-- Timeline items preenchidos via JS -->
                    </div>
                    
                    <!-- Input rápido de nota -->
                    <div class="mt-6 pt-6 border-t border-white/10">
                        <div class="flex gap-3">
                            <input type="text" id="nota-rapida-input" placeholder="Adicionar nota rápida..." 
                                   class="flex-1 bg-black/30 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-orange-500 outline-none">
                            <button onclick="enviarNotaRapida()" class="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-lg font-bold transition-colors">
                                <i class="ph ph-paper-plane-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let timelineAtual = { id_proposta: null, id_cliente: null };

function verHistorico(idProposta) {
    timelineAtual.id_proposta = idProposta;
    document.getElementById('modal-timeline').classList.remove('hidden');
    document.getElementById('timeline-id').textContent = idProposta;
    
    carregarTimeline(idProposta);
}

function fecharTimeline() {
    document.getElementById('modal-timeline').classList.add('hidden');
    timelineAtual = { id_proposta: null, id_cliente: null };
}

function carregarTimeline(idProposta) {
    fetch(`api/timeline_api.php?acao=timeline_proposta&id_proposta=${idProposta}`)
        .then(r => r.json())
        .then(data => {
            if (!data.sucesso) {
                alert('Erro: ' + data.erro);
                return;
            }

            const proposta = data.proposta;
            timelineAtual.id_cliente = proposta.id_cliente;
            
            // Preenche header
            document.getElementById('timeline-titulo').textContent = proposta.nome_cliente;
            document.getElementById('timeline-subtitulo').innerHTML = `
                Proposta #${proposta.id_proposta} • 
                <span class="${getStatusColor(proposta.status)}">${proposta.status}</span> •
                R$ ${parseFloat(proposta.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
            `;

            // Preenche sidebar
            document.getElementById('timeline-info').innerHTML = `
                <div class="text-center mb-4">
                    <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-3xl font-bold text-white mb-3">
                        ${proposta.nome_cliente.charAt(0).toUpperCase()}
                    </div>
                    <h3 class="font-bold text-white text-lg">${proposta.nome_cliente}</h3>
                    <p class="text-sm text-slate-400">${proposta.empresa || 'Pessoa Física'}</p>
                </div>
                
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-2 text-slate-300">
                        <i class="ph ph-whatsapp-logo text-green-400"></i>
                        <span>${proposta.whatsapp || 'Não informado'}</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-300">
                        <i class="ph ph-envelope text-blue-400"></i>
                        <span class="truncate">${proposta.email || 'Não informado'}</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-300">
                        <i class="ph ph-calendar text-orange-400"></i>
                        <span>Criada em ${new Date(proposta.data_criacao).toLocaleDateString('pt-BR')}</span>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-white/5 rounded-lg">
                    <div class="text-xs text-slate-400 uppercase mb-1">Valor da Proposta</div>
                    <div class="text-xl font-bold text-white">
                        R$ ${parseFloat(proposta.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </div>
                </div>
            `;

            // Preenche timeline
            const container = document.getElementById('timeline-content');
            if (data.timeline.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 text-slate-500">
                        <i class="ph ph-clock-counter-clockwise text-4xl mb-3 opacity-30"></i>
                        <p>Nenhuma atividade registrada ainda.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            let dataAtual = '';
            
            data.timeline.forEach(item => {
                const dataItem = item.data_formatada.split(' ')[0];
                
                // Separador de data
                if (dataItem !== dataAtual) {
                    dataAtual = dataItem;
                    html += `
                        <div class="flex items-center gap-4 my-6">
                            <div class="h-px bg-white/10 flex-1"></div>
                            <span class="text-xs font-bold text-slate-500 uppercase">${dataItem}</span>
                            <div class="h-px bg-white/10 flex-1"></div>
                        </div>
                    `;
                }

                html += `
                    <div class="flex gap-4 group">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full bg-${item.cor}-500/20 text-${item.cor}-400 flex items-center justify-center border border-${item.cor}-500/30">
                                <i class="ph ${item.icone} text-lg"></i>
                            </div>
                            <div class="w-px h-full bg-white/10 my-2 group-last:hidden"></div>
                        </div>
                        
                        <div class="flex-1 pb-6">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-white text-sm">${item.titulo}</h4>
                                <span class="text-xs text-slate-500">${item.hora_formatada}</span>
                            </div>
                            <p class="text-sm text-slate-300 mb-2">${item.conteudo}</p>
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <i class="ph ph-user"></i>
                                <span>${item.nome_usuario || 'Sistema'}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        });
}

function getStatusColor(status) {
    const cores = {
        'Em Elaboração': 'text-yellow-400',
        'Enviada': 'text-blue-400',
        'Aprovada': 'text-green-400',
        'Cancelada': 'text-red-400',
        'Finalizada': 'text-purple-400'
    };
    return cores[status] || 'text-slate-400';
}

function abrirModalNovaTarefaFromTimeline() {
    fecharTimeline();
    abrirModalNovaTarefa(timelineAtual.id_proposta);
}

function abrirNotaRapida() {
    document.getElementById('nota-rapida-input').focus();
}

function enviarNotaRapida() {
    const input = document.getElementById('nota-rapida-input');
    const conteudo = input.value.trim();
    
    if (!conteudo) return;
    
    fetch('api/timeline_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            acao: 'adicionar_nota',
            id_proposta: timelineAtual.id_proposta,
            conteudo: conteudo
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.sucesso) {
            input.value = '';
            carregarTimeline(timelineAtual.id_proposta);
            mostrarNotificacao('Nota adicionada!', 'success');
        }
    });
}

// Fechar modal ao clicar fora
document.getElementById('modal-timeline').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) fecharTimeline();
});
</script>