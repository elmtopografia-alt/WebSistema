 <?php
 //(Gerenciador de Arquivos)
// modulo_documentos.php - Interface de gestão de documentos
?>
<!-- Modal Documentos -->
<div id="modal-documentos" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 py-8 flex justify-center">
        <div class="bg-[#111827] w-full max-w-5xl rounded-2xl border border-white/10 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            
            <!-- Header -->
            <div class="p-6 border-b border-white/10 bg-gradient-to-r from-purple-600/20 to-transparent flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                        <i class="ph ph-folder-open text-purple-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Documentos</h3>
                        <p class="text-sm text-slate-400" id="docs-subtitulo">Gerencie arquivos da proposta</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right mr-4">
                        <div class="text-xs text-slate-500">Armazenamento usado</div>
                        <div class="text-sm font-bold text-white" id="storage-info">Carregando...</div>
                    </div>
                    <button onclick="fecharModalDocumentos()" class="p-2 hover:bg-white/10 rounded-lg">
                        <i class="ph ph-x text-xl text-slate-400"></i>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-hidden flex flex-col md:flex-row">
                <!-- Upload Area -->
                <div class="md:w-80 p-6 bg-black/20 border-r border-white/5">
                    <div id="drop-zone" class="border-2 border-dashed border-white/20 rounded-xl p-8 text-center hover:border-purple-500/50 hover:bg-purple-500/5 transition-all cursor-pointer"
                         ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)"
                         onclick="document.getElementById('file-input').click()">
                        <i class="ph ph-cloud-arrow-up text-4xl text-slate-500 mb-3 block"></i>
                        <p class="text-sm text-slate-400 mb-2">Arraste arquivos aqui</p>
                        <p class="text-xs text-slate-600">ou clique para selecionar</p>
                        <p class="text-xs text-slate-600 mt-2">PDF, JPG, PNG, DOC, XLS (max 10MB)</p>
                    </div>
                    
                    <input type="file" id="file-input" class="hidden" multiple onchange="handleFileSelect(event)">
                    
                    <!-- Fila de upload -->
                    <div id="upload-queue" class="mt-4 space-y-2 hidden">
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Enviando...</h4>
                    </div>

                    <!-- Categorias rápidas -->
                    <div class="mt-6">
                        <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">Categorias</h4>
                        <div class="space-y-1">
                            <button onclick="filtrarCategoria('todos')" class="w-full text-left px-3 py-2 rounded-lg text-sm text-white bg-white/10 flex items-center gap-2">
                                <i class="ph ph-squares-four"></i> Todos
                            </button>
                            <button onclick="filtrarCategoria('proposta')" class="w-full text-left px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-white/5 flex items-center gap-2">
                                <i class="ph ph-file-text text-blue-400"></i> Propostas
                            </button>
                            <button onclick="filtrarCategoria('contrato')" class="w-full text-left px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-white/5 flex items-center gap-2">
                                <i class="ph ph-file-doc text-green-400"></i> Contratos
                            </button>
                            <button onclick="filtrarCategoria('comprovante')" class="w-full text-left px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-white/5 flex items-center gap-2">
                                <i class="ph ph-receipt text-purple-400"></i> Comprovantes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lista de Documentos -->
                <div class="flex-1 p-6 overflow-y-auto">
                    <div id="documentos-lista" class="space-y-6">
                        <div class="text-center py-12 text-slate-500">
                            <i class="ph ph-spinner animate-spin text-3xl mb-3"></i>
                            <p>Carregando documentos...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let docPropostaAtual = null;
let categoriaFiltro = 'todos';

function abrirModalDocumentos(idProposta) {
    docPropostaAtual = idProposta;
    document.getElementById('modal-documentos').classList.remove('hidden');
    carregarDocumentos(idProposta);
    carregarEstatisticas();
}

function fecharModalDocumentos() {
    document.getElementById('modal-documentos').classList.add('hidden');
    docPropostaAtual = null;
}

function carregarDocumentos(idProposta) {
    fetch(`api/documentos_api.php?acao=listar&id_proposta=${idProposta}`)
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                renderizarDocumentos(data.documentos);
            }
        });
}

function renderizarDocumentos(documentos) {
    const container = document.getElementById('documentos-lista');
    
    if (Object.keys(documentos).length === 0) {
        container.innerHTML = `
            <div class="text-center py-12 text-slate-500">
                <i class="ph ph-folder-open text-5xl mb-4 opacity-30"></i>
                <p>Nenhum documento encontrado</p>
                <p class="text-sm mt-2">Arraste arquivos ou clique no botão de upload</p>
            </div>
        `;
        return;
    }

    let html = '';
    
    for (const [categoria, arquivos] of Object.entries(documentos)) {
        if (categoriaFiltro !== 'todos' && categoria !== categoriaFiltro) continue;
        
        const titulos = {
            'proposta': 'Propostas',
            'contrato': 'Contratos',
            'comprovante': 'Comprovantes',
            'nota_fiscal': 'Notas Fiscais',
            'documento_cliente': 'Documentos do Cliente',
            'outro': 'Outros'
        };

        html += `
        <div class="mb-6">
            <h4 class="text-sm font-bold text-slate-400 uppercase mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-${arquivos[0].cor}-500"></span>
                ${titulos[categoria] || categoria}
                <span class="text-xs text-slate-600 normal-case">(${arquivos.length})</span>
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                ${arquivos.map(doc => `
                <div class="group bg-white/5 hover:bg-white/10 rounded-lg p-3 border border-white/5 hover:border-white/10 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-${doc.cor}-500/20 flex items-center justify-center flex-shrink-0">
                        <i class="ph ${doc.icone} text-${doc.cor}-400 text-xl"></i>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-white text-sm truncate" title="${doc.nome_original}">${doc.nome_original}</p>
                            ${doc.is_principal ? '<span class="px-1.5 py-0.5 bg-yellow-500/20 text-yellow-400 text-[10px] rounded">Principal</span>' : ''}
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                            <span>${doc.tamanho_formatado}</span>
                            <span>•</span>
                            <span>${doc.data_formatada}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="downloadDocumento(${doc.id_documento})" class="p-2 hover:bg-white/10 rounded text-slate-400 hover:text-white" title="Download">
                            <i class="ph ph-download-simple"></i>
                        </button>
                        <button onclick="definirPrincipal(${doc.id_documento})" class="p-2 hover:bg-yellow-500/20 rounded text-slate-400 hover:text-yellow-400 ${doc.is_principal ? 'hidden' : ''}" title="Definir como principal">
                            <i class="ph ph-star"></i>
                        </button>
                        <button onclick="excluirDocumento(${doc.id_documento})" class="p-2 hover:bg-red-500/20 rounded text-slate-400 hover:text-red-400" title="Excluir">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </div>
                `).join('')}
            </div>
        </div>
        `;
    }
    
    container.innerHTML = html;
}

function handleDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('border-purple-500', 'bg-purple-500/10');
}

function handleDragLeave(e) {
    e.currentTarget.classList.remove('border-purple-500', 'bg-purple-500/10');
}

function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('border-purple-500', 'bg-purple-500/10');
    const files = e.dataTransfer.files;
    processarFiles(files);
}

function handleFileSelect(e) {
    processarFiles(e.target.files);
}

function processarFiles(files) {
    const queue = document.getElementById('upload-queue');
    queue.classList.remove('hidden');
    
    Array.from(files).forEach(file => {
        // Validações
        if (file.size > 10 * 1024 * 1024) {
            mostrarNotificacao(`Arquivo ${file.name} muito grande (max 10MB)`, 'error');
            return;
        }
        
        const item = document.createElement('div');
        item.className = 'flex items-center gap-2 p-2 bg-white/5 rounded text-sm';
        item.innerHTML = `
            <i class="ph ph-file text-slate-400"></i>
            <span class="flex-1 truncate text-slate-300">${file.name}</span>
            <span class="text-xs text-slate-500 status">Enviando...</span>
        `;
        queue.appendChild(item);
        
        uploadFile(file, item);
    });
}

function uploadFile(file, uiElement) {
    const formData = new FormData();
    formData.append('acao', 'upload');
    formData.append('arquivo', file);
    formData.append('id_proposta', docPropostaAtual);
    formData.append('categoria', detectarCategoria(file.name));
    
    fetch('api/documentos_api.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.sucesso) {
            uiElement.querySelector('.status').textContent = '✓';
            uiElement.querySelector('.status').classList.add('text-green-400');
            carregarDocumentos(docPropostaAtual);
            carregarEstatisticas();
            
            // Atualiza timeline se aberta
            if (timelineAtual.id_proposta === docPropostaAtual) {
                carregarTimeline(docPropostaAtual);
            }
        } else {
            uiElement.querySelector('.status').textContent = 'Erro';
            uiElement.querySelector('.status').classList.add('text-red-400');
            mostrarNotificacao(res.erro, 'error');
        }
    })
    .catch(err => {
        uiElement.querySelector('.status').textContent = 'Erro';
        uiElement.querySelector('.status').classList.add('text-red-400');
    });
}

function detectarCategoria(nomeArquivo) {
    const nome = nomeArquivo.toLowerCase();
    if (nome.includes('proposta')) return 'proposta';
    if (nome.includes('contrato')) return 'contrato';
    if (nome.includes('comprovante') || nome.includes('pagamento')) return 'comprovante';
    if (nome.includes('nota') || nome.includes('nfe')) return 'nota_fiscal';
    return 'outro';
}

function downloadDocumento(id) {
    fetch(`api/documentos_api.php?acao=download&id_documento=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.sucesso) {
                const a = document.createElement('a');
                a.href = res.url;
                a.download = res.nome;
                a.click();
            }
        });
}

function definirPrincipal(id) {
    fetch('api/documentos_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ acao: 'definir_principal', id_documento: id })
    })
    .then(r => r.json())
    .then(res => {
        if (res.sucesso) {
            carregarDocumentos(docPropostaAtual);
        }
    });
}

function excluirDocumento(id) {
    if (!confirm('Tem certeza que deseja excluir este documento?')) return;
    
    fetch('api/documentos_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ acao: 'excluir', id_documento: id })
    })
    .then(r => r.json())
    .then(res => {
        if (res.sucesso) {
            carregarDocumentos(docPropostaAtual);
            carregarEstatisticas();
        }
    });
}

function carregarEstatisticas() {
    fetch('api/documentos_api.php?acao=estatisticas_armazenamento')
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                document.getElementById('storage-info').textContent = 
                    `${data.total_arquivos} arquivos • ${data.total_armazenamento}`;
            }
        });
}

function filtrarCategoria(cat) {
    categoriaFiltro = cat;
    
    // Atualiza UI
    document.querySelectorAll('[onclick^="filtrarCategoria"]').forEach(btn => {
        btn.classList.remove('bg-white/10', 'text-white');
        btn.classList.add('text-slate-400');
    });
    event.target.closest('button').classList.remove('text-slate-400');
    event.target.closest('button').classList.add('bg-white/10', 'text-white');
    
    carregarDocumentos(docPropostaAtual);
}
</script>