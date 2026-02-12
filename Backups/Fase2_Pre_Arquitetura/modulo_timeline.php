<?php
// modulo_timeline.php
// Módulo de Histórico/Timeline do CRM
?>
<!-- Modal Timeline -->
<div id="modalTimeline" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-[60] items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-white/10 rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl">
        <div class="p-4 border-b border-white/5 flex justify-between items-center bg-white/5">
            <h3 class="font-bold text-lg"><i class="ph ph-clock-counter-clockwise text-purple-400 mr-2"></i> Timeline: <span id="timelineTitle" class="text-white"></span></h3>
            <button onclick="fecharModal('modalTimeline')" class="p-2 hover:bg-white/10 rounded-full"><i class="ph ph-x"></i></button>
        </div>
        <div id="timelineContent" class="flex-1 overflow-y-auto p-6 space-y-6 relative">
            <!-- Conteúdo Injetado via JS -->
        </div>
        <div class="p-4 border-t border-white/5 bg-white/5">
            <div class="flex gap-2">
                <input type="text" id="notaInput" placeholder="Adicionar nota rápida..." class="flex-1 bg-black/30 border border-white/10 rounded-lg px-4 py-2 text-sm focus:border-purple-500 outline-none text-white">
                <button onclick="enviarNota()" class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-lg font-bold"><i class="ph ph-paper-plane-right"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Funções Timeline CRM 3.0 ---
    let currentPropId = 0;

    // Função auxiliar global se não existir
    if (typeof fecharModal !== 'function') {
        window.fecharModal = function(id) {
            document.getElementById(id).classList.add('hidden');
            document.getElementById(id).classList.remove('flex');
        }
    }

    function abrirTimeline(id, nome) {
        currentPropId = id;
        document.getElementById('timelineTitle').innerText = nome;
        document.getElementById('modalTimeline').classList.remove('hidden');
        document.getElementById('modalTimeline').classList.add('flex');
        
        // Carregar dados
        document.getElementById('timelineContent').innerHTML = '<div class="text-center py-10"><i class="ph ph-spinner animate-spin text-3xl text-purple-500"></i></div>';
        
        fetch(`api/timeline_api.php?acao=timeline_proposta&id_proposta=${id}`)
            .then(r => r.json())
            .then(data => {
                if(!data.sucesso) throw new Error(data.erro);
                let html = '<div class="absolute left-6 top-6 bottom-6 w-0.5 bg-white/10"></div>'; // Linha vertical
                
                if(data.timeline.length === 0) {
                    html += '<div class="ml-10 text-slate-500 italic">Nenhuma atividade registrada.</div>';
                }

                data.timeline.forEach(item => {
                    html += `
                    <div class="relative pl-12">
                        <div class="absolute left-4 -translate-x-1/2 w-4 h-4 rounded-full bg-[#0f172a] border-2 border-${item.cor}-500 z-10"></div>
                        <div class="bg-white/5 rounded-lg p-3 border border-white/5">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-xs font-bold text-${item.cor}-400">${item.titulo}</span>
                                <span class="text-[10px] text-slate-500">${item.data_formatada}</span>
                            </div>
                            <p class="text-sm text-slate-300">${item.conteudo}</p>
                            <div class="mt-1 text-[10px] text-slate-600">Por: ${item.nome_usuario || 'Sistema'}</div>
                        </div>
                    </div>`;
                });
                document.getElementById('timelineContent').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('timelineContent').innerHTML = `<div class="text-center text-red-400 py-4">Erro: ${err.message}</div>`;
            });
    }

    function enviarNota() {
        const nota = document.getElementById('notaInput').value;
        if(!nota.trim()) return;
        
        fetch('api/timeline_api.php', {
            method: 'POST',
            body: JSON.stringify({ acao: 'adicionar_nota', id_proposta: currentPropId, conteudo: nota })
        })
        .then(r => r.json())
        .then(data => {
            if(data.sucesso) {
                document.getElementById('notaInput').value = '';
                abrirTimeline(currentPropId, document.getElementById('timelineTitle').innerText); // Recarrega
            }
        });
    }
</script>
