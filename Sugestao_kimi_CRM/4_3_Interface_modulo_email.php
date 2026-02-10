<?php
//(Composer de Email)
// modulo_email.php - Interface de composição de emails
?>
<!-- Modal Composer de Email -->
<div id="modal-email" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 py-8 flex justify-center">
        <div class="bg-[#111827] w-full max-w-4xl rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
            
            <!-- Header -->
            <div class="p-6 border-b border-white/10 bg-gradient-to-r from-blue-600/20 to-transparent flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                        <i class="ph ph-envelope-simple text-blue-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Novo Email</h3>
                        <p class="text-sm text-slate-400" id="email-subtitulo">Selecione um template ou escreva manualmente</p>
                    </div>
                </div>
                <button onclick="fecharModalEmail()" class="p-2 hover:bg-white/10 rounded-lg">
                    <i class="ph ph-x text-xl text-slate-400"></i>
                </button>
            </div>

            <div class="grid md:grid-cols-3 gap-0">
                <!-- Sidebar: Templates -->
                <div class="md:col-span-1 p-6 bg-black/20 border-r border-white/5">
                    <div class="mb-4">
                        <h4 class="text-sm font-bold text-slate-400 uppercase mb-3">Templates</h4>
                        <div class="space-y-2" id="lista-templates">
                            <div class="animate-pulse space-y-2">
                                <div class="h-10 bg-white/5 rounded"></div>
                                <div class="h-10 bg-white/5 rounded"></div>
                                <div class="h-10 bg-white/5 rounded"></div>
                            </div>
                        </div>
                    </div>
                    
                    <button onclick="criarNovoTemplate()" class="w-full py-2 border border-dashed border-white/20 text-slate-400 hover:text-white hover:border-white/40 rounded-lg text-sm transition-colors">
                        <i class="ph ph-plus mr-1"></i> Criar Template
                    </button>

                    <!-- Dicas de variáveis -->
                    <div class="mt-6 p-4 bg-white/5 rounded-lg">
                        <h5 class="text-xs font-bold text-slate-400 uppercase mb-2">Variáveis disponíveis</h5>
                        <div class="space-y-1 text-xs text-slate-500">
                            <code class="block bg-black/30 px-2 py-1 rounded">{nome_cliente}</code>
                            <code class="block bg-black/30 px-2 py-1 rounded">{nome_empresa}</code>
                            <code class="block bg-black/30 px-2 py-1 rounded">{valor_proposta}</code>
                            <code class="block bg-black/30 px-2 py-1 rounded">{id_proposta}</code>
                            <code class="block bg-black/30 px-2 py-1 rounded">{data_atual}</code>
                        </div>
                    </div>
                </div>

                <!-- Formulário -->
                <div class="md:col-span-2 p-6">
                    <form id="form-email" onsubmit="enviarEmail(event)">
                        <input type="hidden" id="email_id_proposta" name="id_proposta">
                        <input type="hidden" id="email_id_template" name="id_template">
                        
                        <div class="space-y-4">
                            <!-- Destinatário -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Para</label>
                                <div class="flex gap-2">
                                    <input type="email" id="email_destinatario" name="destinatario" required
                                           class="flex-1 bg-black/30 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-blue-500 outline-none"
                                           placeholder="cliente@empresa.com">
                                    <button type="button" onclick="buscarEmailCliente()" class="px-4 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-slate-400">
                                        <i class="ph ph-magnifying-glass"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Assunto -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Assunto</label>
                                <input type="text" id="email_assunto" name="assunto" required
                                       class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-blue-500 outline-none"
                                       placeholder="Assunto do email">
                            </div>

                            <!-- Corpo -->
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Mensagem</label>
                                <textarea id="email_corpo" name="corpo" rows="12" required
                                          class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-blue-500 outline-none resize-none font-mono text-sm"
                                          placeholder="Escreva sua mensagem..."></textarea>
                            </div>

                            <!-- Opções avançadas -->
                            <div class="flex items-center justify-between pt-4 border-t border-white/10">
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" id="email_agendar" name="agendar" class="w-4 h-4 rounded border-white/20 bg-black/30 text-blue-600">
                                        <span class="text-sm text-slate-400">Agendar envio</span>
                                    </label>
                                    <input type="datetime-local" id="email_data_agendamento" name="data_agendamento" 
                                           class="hidden bg-black/30 border border-white/10 rounded-lg px-3 py-1 text-white text-sm"
                                           disabled>
                                </div>
                                
                                <div class="flex gap-3">
                                    <button type="button" onclick="salvarComoTemplate()" class="px-4 py-2 text-slate-400 hover:text-white text-sm font-medium">
                                        Salvar como Template
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold flex items-center gap-2">
                                        <i class="ph ph-paper-plane-right"></i>
                                        <span id="btn-enviar-texto">Enviar Agora</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Preview -->
                    <div id="email-preview" class="mt-6 p-4 bg-white/5 rounded-lg border border-white/10 hidden">
                        <h5 class="text-xs font-bold text-slate-400 uppercase mb-2">Preview</h5>
                        <div class="prose prose-invert max-w-none text-sm text-slate-300" id="email-preview-conteudo"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botão de Email nos Cards (adicione ao painel_crm.php) -->
<script>
// Estado
let emailPropostaAtual = null;
let templatesCache = [];

// Toggle agendamento
document.getElementById('email_agendar')?.addEventListener('change', function() {
    const input = document.getElementById('email_data_agendamento');
    input.classList.toggle('hidden', !this.checked);
    input.disabled = !this.checked;
    if (this.checked) {
        // Default: amanhã 9h
        const amanha = new Date();
        amanha.setDate(amanha.getDate() + 1);
        amanha.setHours(9, 0, 0, 0);
        input.value = amanha.toISOString().slice(0, 16);
    }
    document.getElementById('btn-enviar-texto').textContent = this.checked ? 'Agendar' : 'Enviar Agora';
});

function abrirModalEmail(idProposta) {
    emailPropostaAtual = idProposta;
    document.getElementById('email_id_proposta').value = idProposta;
    document.getElementById('modal-email').classList.remove('hidden');
    
    carregarTemplates();
    prepararEmail(idProposta);
}

function fecharModalEmail() {
    document.getElementById('modal-email').classList.add('hidden');
    document.getElementById('form-email').reset();
    emailPropostaAtual = null;
}

function carregarTemplates() {
    fetch('api/email_api.php?acao=listar_templates')
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                templatesCache = data.templates;
                const container = document.getElementById('lista-templates');
                container.innerHTML = data.templates.map(t => `
                    <div onclick="selecionarTemplate(${t.id_template})" 
                         class="p-3 bg-white/5 hover:bg-white/10 rounded-lg cursor-pointer transition-colors border border-transparent hover:border-blue-500/30 group"
                         data-id="${t.id_template}">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium text-white text-sm group-hover:text-blue-400">${t.nome}</div>
                                <div class="text-xs text-slate-500 mt-1">${t.tipo}</div>
                            </div>
                            <i class="ph ph-envelope text-slate-600 group-hover:text-blue-400"></i>
                        </div>
                    </div>
                `).join('');
            }
        });
}

function selecionarTemplate(idTemplate) {
    document.getElementById('email_id_template').value = idTemplate;
    
    // Destaca visualmente
    document.querySelectorAll('#lista-templates > div').forEach(div => {
        div.classList.remove('border-blue-500', 'bg-blue-500/10');
        if (parseInt(div.dataset.id) === idTemplate) {
            div.classList.add('border-blue-500', 'bg-blue-500/10');
        }
    });
    
    // Recarrega preview com template
    if (emailPropostaAtual) {
        prepararEmail(emailPropostaAtual, idTemplate);
    }
}

function prepararEmail(idProposta, idTemplate = null) {
    const url = `api/email_api.php?acao=preparar&id_proposta=${idProposta}${idTemplate ? '&id_template=' + idTemplate : ''}`;
    
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ acao: 'preparar', id_proposta: idProposta, id_template: idTemplate })
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) {
            const preview = data.preview;
            document.getElementById('email_destinatario').value = preview.destinatario || '';
            document.getElementById('email_assunto').value = preview.preview_assunto || '';
            document.getElementById('email_corpo').value = preview.preview_corpo || '';
            
            if (preview.preview_assunto) {
                mostrarPreview(preview.preview_assunto, preview.preview_corpo);
            }
        }
    });
}

function mostrarPreview(assunto, corpo) {
    const previewDiv = document.getElementById('email-preview');
    const conteudo = document.getElementById('email-preview-conteudo');
    
    previewDiv.classList.remove('hidden');
    conteudo.innerHTML = `
        <div class="border-b border-white/10 pb-2 mb-2">
            <strong>Assunto:</strong> ${assunto}
        </div>
        <div>${corpo.replace(/\n/g, '<br>')}</div>
    `;
}

function enviarEmail(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    const data = {
        acao: 'enviar',
        id_proposta: parseInt(formData.get('id_proposta')),
        id_template: parseInt(formData.get('id_template')) || null,
        destinatario: formData.get('destinatario'),
        assunto: formData.get('assunto'),
        corpo: formData.get('corpo'),
        agendar: formData.get('agendar') === 'on',
        data_agendamento: formData.get('data_agendamento') || null
    };
    
    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Enviando...';
    btn.disabled = true;
    
    fetch('api/email_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        if (res.sucesso) {
            mostrarNotificacao(res.mensagem, 'success');
            fecharModalEmail();
            
            // Atualiza timeline se estiver aberta
            if (timelineAtual.id_proposta === data.id_proposta) {
                carregarTimeline(data.id_proposta);
            }
        } else {
            mostrarNotificacao(res.erro, 'error');
        }
    })
    .catch(err => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        mostrarNotificacao('Erro ao enviar email', 'error');
    });
}

function salvarComoTemplate() {
    const nome = prompt('Nome do template:');
    if (!nome) return;
    
    const data = {
        acao: 'salvar_template',
        nome: nome,
        assunto: document.getElementById('email_assunto').value,
        corpo: document.getElementById('email_corpo').value,
        tipo: 'personalizado'
    };
    
    fetch('api/email_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if (res.sucesso) {
            mostrarNotificacao('Template salvo!', 'success');
            carregarTemplates();
        }
    });
}

function criarNovoTemplate() {
    document.getElementById('email_id_template').value = '';
    document.getElementById('email_assunto').value = '';
    document.getElementById('email_corpo').value = '';
    document.querySelectorAll('#lista-templates > div').forEach(div => {
        div.classList.remove('border-blue-500', 'bg-blue-500/10');
    });
}
</script>

