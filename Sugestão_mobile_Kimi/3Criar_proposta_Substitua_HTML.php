//Substitua o HTML do modal de novo cliente (aproximadamente linhas 1150-1200)
<!-- MODAL NOVO CLIENTE - VERSÃO CORRIGIDA -->
<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal-dialog-custom" id="modalNovoCliente">
    <div class="modal-header-custom">
        <h5><i class="bi bi-person-plus-fill"></i> Novo Cliente</h5>
        <button type="button" class="modal-close-btn" id="btnCloseModal" title="Fechar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="modal-body-custom">
        <form id="form-novo-cliente">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="acao" value="criar_ajax">

            <div class="form-row cols-2">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Nome completo *</label>
                    <input type="text" name="nome_cliente" class="form-control" required placeholder="Digite o nome completo" autocomplete="name">
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Empresa (opcional)</label>
                    <input type="text" name="empresa" class="form-control" placeholder="Nome da empresa" autocomplete="organization">
                </div>
                
                <div class="form-group">
                    <label class="form-label">CPF/CNPJ</label>
                    <input type="text" name="cnpj_cpf" class="form-control" placeholder="000.000.000-00" inputmode="numeric">
                </div>
                
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" placeholder="email@exemplo.com" autocomplete="email" inputmode="email">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Celular *</label>
                    <input type="tel" name="celular" class="form-control" placeholder="(31) 99999-9999" required autocomplete="tel" inputmode="tel">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Telefone fixo</label>
                    <input type="tel" name="telefone" class="form-control" placeholder="(31) 3333-3333" autocomplete="tel" inputmode="tel">
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer-custom">
        <button type="button" class="btn btn-outline" id="btnCancelarModal">
            <i class="bi bi-x-lg"></i> Cancelar
        </button>
        <button type="button" class="btn btn-success" id="btn-salvar-cliente">
            <i class="bi bi-check-lg"></i> Salvar Cliente
        </button>
    </div>
</div>