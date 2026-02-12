<?php
// modulo_email.php
// Módulo de Email do CRM
?>
<!-- Modal Email -->
<div id="modal-email" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-[60] items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-white/10 rounded-2xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-2xl">
        <div class="p-4 border-b border-white/5 flex justify-between items-center bg-white/5">
            <h3 class="font-bold text-lg"><i class="ph ph-envelope-simple text-indigo-400 mr-2"></i> Enviar Email</h3>
            <button onclick="fecharModal('modal-email')" class="p-2 hover:bg-white/10 rounded-full"><i class="ph ph-x"></i></button>
        </div>
        
        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar Templates -->
            <div class="w-64 border-r border-white/5 bg-black/20 p-4 overflow-y-auto hidden md:block">
                <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">Modelos</h4>
                <div id="lista-templates" class="space-y-1">
                    <!-- Templates carregados via JS -->
                    <div class="text-center text-xs text-slate-600">Carregando...</div>
                </div>
                <button onclick="salvarComoTemplate()" class="w-full mt-4 py-2 border border-white/10 rounded text-xs text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
                    + Salvar Atual Como Modelo
                </button>
            </div>
            
            <!-- Form -->
            <div class="flex-1 p-6 overflow-y-auto">
                <form onsubmit="enviarEmail(event)" class="space-y-4">
                    <input type="hidden" id="email_id_proposta">
                    <input type="hidden" id="email_id_template">
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Para</label>
                            <input type="email" id="email_destinatario" required class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Assunto</label>
                            <input type="text" id="email_assunto" required class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Mensagem</label>
                        <textarea id="email_corpo" rows="12" required class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none font-mono text-sm leading-relaxed"></textarea>
                        <p class="text-[10px] text-slate-500 mt-1">Variáveis disponíveis: {cliente}, {proposta_id}, {valor}, {link_proposta}</p>
                    </div>
                    
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg font-bold shadow-lg shadow-indigo-900/20 flex items-center gap-2">
                            <i class="ph ph-paper-plane-right"></i> Enviar Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- ... HTML acima ... -->
<script>
    // --- Lógica de Email ---
    let emailPropId = 0;

    function abrirModalEmail(id, emailCliente) {
        emailPropId = id;
        document.getElementById('email_id_proposta').value = id;
        document.getElementById('email_destinatario').value = emailCliente || '';
        document.getElementById('modal-email').classList.remove('hidden');
        document.getElementById('modal-email').classList.add('flex');
        
        carregarTemplates();
    }

    function carregarTemplates() {
        const container = document.getElementById('lista-templates');
        
        fetch('api/email_api.php?acao=listar_templates')
        .then(r => r.json())
        .then(data => {
            if(!data.sucesso) {
                container.innerHTML = '<div class="text-xs text-red-400">Erro ao carregar</div>';
                return;
            }
            
            if(data.templates.length === 0) {
                container.innerHTML = '<div class="text-xs text-slate-500 italic">Sem modelos salvos.</div>';
                return;
            }
            
            let html = '';
            data.templates.forEach(t => {
                html += `
                <div onclick="usarTemplate(${t.id_template})" class="cursor-pointer group p-2 rounded hover:bg-white/5 border border-transparent hover:border-white/5 transition-all">
                    <div class="text-xs font-bold text-slate-300 group-hover:text-white truncate">${t.nome}</div>
                    <div class="text-[10px] text-slate-500 truncate">${t.assunto}</div>
                </div>`;
            });
            container.innerHTML = html;
        });
    }

    function usarTemplate(id) {
        fetch(`api/email_api.php?acao=obter_template&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if(data.sucesso) {
                document.getElementById('email_assunto').value = data.template.assunto;
                document.getElementById('email_corpo').value = data.template.corpo;
                document.getElementById('email_id_template').value = id;
            }
        });
    }

    function salvarComoTemplate() {
        const nome = prompt("Nome do modelo:");
        if(!nome) return;
        
        const assunto = document.getElementById('email_assunto').value;
        const corpo = document.getElementById('email_corpo').value;
        
        fetch('api/email_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                acao: 'salvar_template',
                nome: nome,
                assunto: assunto,
                corpo: corpo
            })
        })
        .then(r => r.json())
        .then(data => {
            if(data.sucesso) {
                alert('Modelo salvo!');
                carregarTemplates();
            } else {
                alert('Erro: ' + data.erro);
            }
        });
    }

    function enviarEmail(e) {
        e.preventDefault();
        
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Enviando...';
        
        const dados = {
            acao: 'enviar_email',
            id_proposta: emailPropId,
            destinatario: document.getElementById('email_destinatario').value,
            assunto: document.getElementById('email_assunto').value,
            corpo: document.getElementById('email_corpo').value,
            id_template: document.getElementById('email_id_template').value
        };
        
        fetch('api/email_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(dados)
        })
        .then(r => r.json())
        .then(data => {
            if(data.sucesso) {
                alert('Email enviado com sucesso!');
                fecharModal('modal-email');
                document.getElementById('email_corpo').value = ''; // Limpa corpo
            } else {
                alert('Erro ao enviar: ' + data.erro);
            }
        })
        .catch(err => alert('Erro de conexão: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    // Export e Fallback
    window.abrirModalEmail = abrirModalEmail;
</script>
