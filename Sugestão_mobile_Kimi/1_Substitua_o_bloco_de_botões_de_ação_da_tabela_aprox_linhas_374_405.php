//Vou criar os arquivos corrigidos para você. Como não posso gerar arquivos diretamente, vou fornecer o código completo das seções modificadas para você copiar e colar.

<!-- BLOCO DE BOTÕES DE AÇÃO - VERSÃO MOBILE CORRIGIDA -->
<div class="flex items-center justify-end gap-2 mt-2">
    <!-- Editar -->
    <a href="editar_proposta.php?id=<?= $id ?>" 
       class="w-10 h-10 md:w-auto md:h-auto md:px-3 md:py-1.5 rounded-lg bg-yellow-500/10 text-yellow-500 hover:bg-yellow-500 hover:text-white flex items-center justify-center transition-all touch-target" 
       title="Editar">
        <i class="ph ph-pencil-simple text-lg"></i>
        <span class="hidden md:inline ml-1 text-xs font-medium">Editar</span>
    </a>
    
    <!-- Relatório -->
    <a href="relatorio_proposta.php?id=<?= $id ?>" 
       class="w-10 h-10 md:w-auto md:h-auto md:px-3 md:py-1.5 rounded-lg bg-orange-500/10 text-orange-400 hover:bg-orange-500 hover:text-white flex items-center justify-center transition-all touch-target" 
       title="Relatório">
        <i class="ph ph-file-text text-lg"></i>
        <span class="hidden md:inline ml-1 text-xs font-medium">Relatório</span>
    </a>

    <?php if ($arquivoExiste): ?>
        <!-- Email -->
        <a href="enviar_email.php?id=<?= $id ?>" 
           class="w-10 h-10 md:w-auto md:h-auto md:px-3 md:py-1.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500 hover:text-white flex items-center justify-center transition-all touch-target" 
           title="Enviar Email">
            <i class="ph ph-paper-plane-tilt text-lg"></i>
            <span class="hidden md:inline ml-1 text-xs font-medium">Email</span>
        </a>
        
        <!-- Download -->
        <a href="<?= $caminhoFinal ?>" download 
           class="w-10 h-10 md:w-auto md:h-auto md:px-3 md:py-1.5 rounded-lg bg-green-500/10 text-green-400 hover:bg-green-500 hover:text-white flex items-center justify-center transition-all touch-target" 
           title="Baixar DOCX">
            <i class="ph ph-download-simple text-lg"></i>
            <span class="hidden md:inline ml-1 text-xs font-medium">Download</span>
        </a>
    <?php else: ?>
        <!-- Arquivo não encontrado -->
        <span class="w-10 h-10 rounded-lg bg-slate-700/30 text-slate-600 flex items-center justify-center cursor-not-allowed touch-target" title="Arquivo não encontrado">
            <i class="ph ph-file-dashed text-lg"></i>
        </span>
    <?php endif; ?>
</div>