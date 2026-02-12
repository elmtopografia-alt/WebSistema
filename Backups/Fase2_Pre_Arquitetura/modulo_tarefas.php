<?php
// modulo_tarefas.php
// Módulo de Tarefas do CRM
// Inclui o modal e qualquer lógica de frontend específica para tarefas
?>
<!-- Modal Nova Tarefa -->
<div id="modalTarefa" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-[60] items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-white/10 rounded-2xl w-full max-w-md flex flex-col shadow-2xl">
        <div class="p-4 border-b border-white/5 flex justify-between items-center bg-white/5">
            <h3 class="font-bold text-lg"><i class="ph ph-calendar-plus text-orange-400 mr-2"></i> Nova Tarefa</h3>
            <button onclick="fecharModal('modalTarefa')" class="p-2 hover:bg-white/10 rounded-full"><i class="ph ph-x"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="taskPropId">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Tipo de Ação</label>
                <div class="grid grid-cols-3 gap-2">
                    <button onclick="selTipo('ligacao', this)" class="tipo-btn active bg-white/10 border border-white/20 rounded p-2 text-center text-xs hover:bg-white/20 transition-all text-white" data-val="ligacao"><i class="ph ph-phone text-lg mb-1 block"></i> Ligação</button>
                    <button onclick="selTipo('whatsapp', this)" class="tipo-btn bg-black/20 border border-white/5 rounded p-2 text-center text-xs hover:bg-white/10 transition-all text-slate-400" data-val="whatsapp"><i class="ph ph-whatsapp-logo text-lg mb-1 block"></i> Zap</button>
                    <button onclick="selTipo('reuniao', this)" class="tipo-btn bg-black/20 border border-white/5 rounded p-2 text-center text-xs hover:bg-white/10 transition-all text-slate-400" data-val="reuniao"><i class="ph ph-users text-lg mb-1 block"></i> Reunião</button>
                </div>
                <input type="hidden" id="taskTipo" value="ligacao">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Data e Hora</label>
                <input type="datetime-local" id="taskData" class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Descrição</label>
                <textarea id="taskDesc" rows="2" class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none" placeholder="Ex: Ligar para cobrar retorno..."></textarea>
            </div>
            <button onclick="salvarTarefa()" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-3 rounded-xl shadow-lg shadow-orange-900/20">Agendar Tarefa</button>
        </div>
    </div>
</div>

<script>
    // Lógica de Tarefas
    function novaTarefa(id, nome) {
        document.getElementById('taskPropId').value = id;
        // Define data padrão para amanhã 09:00
        const amanha = new Date();
        amanha.setDate(amanha.getDate() + 1);
        amanha.setHours(9, 0, 0, 0);
        
        // Ajuste de fuso horário local para ISO string
        const offset = amanha.getTimezoneOffset() * 60000;
        const localISOTime = (new Date(amanha - offset)).toISOString().slice(0, 16);
        
        document.getElementById('taskData').value = localISOTime;
        
        document.getElementById('modalTarefa').classList.remove('hidden');
        document.getElementById('modalTarefa').classList.add('flex');
    }

    function selTipo(tipo, btn) {
        document.getElementById('taskTipo').value = tipo;
        document.querySelectorAll('.tipo-btn').forEach(b => {
            b.classList.remove('active', 'bg-white/10', 'text-white');
            b.classList.add('bg-black/20', 'text-slate-400');
        });
        btn.classList.remove('bg-black/20', 'text-slate-400');
        btn.classList.add('active', 'bg-white/10', 'text-white');
    }

    function salvarTarefa() {
        const id = document.getElementById('taskPropId').value;
        const tipo = document.getElementById('taskTipo').value;
        const data = document.getElementById('taskData').value;
        const desc = document.getElementById('taskDesc').value;
        
        // Botão loading?
        
        fetch('api/tarefas_api.php', {
            method: 'POST',
            body: JSON.stringify({ acao: 'criar', id_proposta: id, tipo, data_agendada: data, descricao: desc })
        })
        .then(r => r.json())
        .then(data => {
            if(data.sucesso) {
                alert('Tarefa agendada!');
                fecharModal('modalTarefa');
                // Se possível, atualizar contador sem reload total, mas reload é seguro
                location.reload(); 
            } else {
                alert('Erro: ' + data.erro);
            }
        });
    }
    
    // Função global necessária para ser chamada de fora
    window.abrirModalNovaTarefa = novaTarefa;
</script>
