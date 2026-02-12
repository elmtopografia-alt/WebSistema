<?php
// modulo_documentos.php
// Módulo de Documentos do CRM
?>
<!-- Modal Documentos -->
<div id="modal-documentos" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-[60] items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-white/10 rounded-2xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-2xl">
        <div class="p-4 border-b border-white/5 flex justify-between items-center bg-white/5">
            <h3 class="font-bold text-lg"><i class="ph ph-folder-open text-pink-400 mr-2"></i> Documentos da Proposta</h3>
            <button onclick="fecharModal('modal-documentos')" class="p-2 hover:bg-white/10 rounded-full"><i class="ph ph-x"></i></button>
        </div>
        
        <div class="p-4 bg-black/20 border-b border-white/5">
            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-white/10 rounded-xl cursor-pointer hover:border-pink-500/50 hover:bg-pink-500/5 transition-all group">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <i class="ph ph-cloud-arrow-up text-3xl text-slate-400 group-hover:text-pink-400 mb-2 transition-colors"></i>
                    <p class="text-sm text-slate-400 group-hover:text-white"><span class="font-semibold">Clique para enviar</span> ou arraste e solte</p>
                    <p class="text-xs text-slate-500">PDF, JPG, PNG, DOCX (Max 10MB)</p>
                </div>
                <input type="file" class="hidden" onchange="uploadDocumento(this)">
            </label>
        </div>

        <div id="lista-documentos-container" class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            <!-- Lista renderizada via JS -->
            <div class="text-center text-slate-500 mt-10">Carregando...</div>
        </div>
    </div>
</div>

<script>
    // --- Lógica de Documentos ---
    let docPropId = 0;

    function abrirModalDocumentos(id) {
        docPropId = id;
        document.getElementById('modal-documentos').classList.remove('hidden');
        document.getElementById('modal-documentos').classList.add('flex');
        listarDocumentos();
    }

    function uploadDocumento(input) {
        if(input.files.length === 0) return;
        
        const file = input.files[0];
        const formData = new FormData();
        formData.append('arquivo', file);
        formData.append('id_proposta', docPropId);
        formData.append('acao', 'upload');
        
        // Show loading state
        document.getElementById('lista-documentos-container').innerHTML = '<div class="text-center py-4"><i class="ph ph-spinner animate-spin text-2xl text-pink-400"></i><p class="text-xs text-slate-500 mt-2">Enviando...</p></div>';
        
        fetch('api/documentos_api.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if(data.sucesso) {
                listarDocumentos(); // Reload list
            } else {
                alert('Erro ao enviar: ' + data.erro);
                listarDocumentos(); // Restore list
            }
            input.value = ''; // Reset input
        })
        .catch(err => {
            alert('Erro de conexão: ' + err.message);
            listarDocumentos();
        });
    }

    function listarDocumentos() {
        const container = document.getElementById('lista-documentos-container');
        container.innerHTML = '<div class="text-center py-4"><i class="ph ph-spinner animate-spin text-2xl text-pink-400"></i></div>';
        
        fetch(`api/documentos_api.php?acao=listar&id_proposta=${docPropId}`)
        .then(r => r.json())
        .then(data => {
            if(!data.sucesso) throw new Error(data.erro);
            
            if(data.documentos.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-10 opacity-50">
                        <i class="ph ph-files text-4xl mb-2"></i>
                        <p class="text-sm">Nenhum documento anexado.</p>
                    </div>`;
                return;
            }
            
            let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">';
            data.documentos.forEach(doc => {
                // Icon based on type
                let icon = 'ph-file';
                let color = 'slate';
                if(doc.tipo.includes('image')) { icon = 'ph-image'; color = 'purple'; }
                else if(doc.tipo.includes('pdf')) { icon = 'ph-file-pdf'; color = 'red'; }
                else if(doc.tipo.includes('word')) { icon = 'ph-file-doc'; color = 'blue'; }
                
                html += `
                <div class="bg-black/30 border border-white/5 rounded-lg p-3 flex items-center justify-between group hover:border-pink-500/30 transition-all">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-10 h-10 rounded bg-${color}-500/10 flex items-center justify-center text-${color}-400">
                            <i class="ph ${icon} text-xl"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-medium text-slate-200 truncate" title="${doc.nome_original}">${doc.nome_original}</h4>
                            <p class="text-xs text-slate-500">${doc.tamanho_formatado} • ${doc.data_upload}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="${doc.url}" target="_blank" class="p-1.5 hover:bg-white/10 rounded text-slate-400 hover:text-white" title="Baixar/Visualizar">
                            <i class="ph ph-download-simple"></i>
                        </a>
                        <button onclick="deletarDocumento(${doc.id})" class="p-1.5 hover:bg-red-500/10 rounded text-slate-400 hover:text-red-400" title="Excluir">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(err => {
            container.innerHTML = `<div class="text-red-400 text-sm text-center">Erro: ${err.message}</div>`;
        });
    }

    function deletarDocumento(idDoc) {
        if(!confirm('Tem certeza que deseja excluir este documento?')) return;
        
        fetch('api/documentos_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ acao: 'deletar', id_documento: idDoc })
        })
        .then(r => r.json())
        .then(data => {
            if(data.sucesso) listarDocumentos();
            else alert('Erro: ' + data.erro);
        });
    }

    // Export global
    window.abrirModalDocumentos = abrirModalDocumentos;
    window.uploadDocumento = uploadDocumento;
    window.deletarDocumento = deletarDocumento;
</script>
