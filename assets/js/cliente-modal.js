/**
 * SGT ClienteModal - Gerenciamento do modal de novo cliente
 */

const ClienteModal = {
    modal: null,
    form: null,
    btnSalvar: null,

    init() {
        this.modal = document.getElementById('modalNovoCliente');
        this.form = document.getElementById('form-novo-cliente');
        this.btnSalvar = document.getElementById('btn-salvar-cliente');

        this.bindEvents();
    },

    bindEvents() {
        // Abrir modal
        document.getElementById('btn-novo-cliente')?.addEventListener('click', () => {
            this.form?.reset();
            this.open();
        });

        // Fechar modal
        document.querySelectorAll('.btn-close-modal').forEach(btn => {
            btn.addEventListener('click', () => this.close());
        });

        // Fechar ao clicar no backdrop
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });

        // Salvar cliente
        this.btnSalvar?.addEventListener('click', () => this.salvar());

        // Máscaras de input
        this.setupMasks();
    },

    setupMasks() {
        const celularInput = this.form?.querySelector('input[name="celular"]');
        const telefoneInput = this.form?.querySelector('input[name="telefone"]');
        const cnpjInput = this.form?.querySelector('input[name="cnpj_cpf"]');

        celularInput?.addEventListener('input', (e) => {
            e.target.value = SGTUtils.maskPhone(e.target.value);
        });

        telefoneInput?.addEventListener('input', (e) => {
            e.target.value = SGTUtils.maskPhone(e.target.value);
        });

        // Máscara simples para CPF/CNPJ
        cnpjInput?.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                // CPF: 000.000.000-00
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                // CNPJ: 00.000.000/0000-00
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
    },

    open() {
        this.modal?.classList.add('show');
        document.body.classList.add('modal-open');
    },

    close() {
        this.modal?.classList.remove('show');
        document.body.classList.remove('modal-open');
    },

    async salvar() {
        const nomeInput = this.form?.querySelector('input[name="nome_cliente"]');
        const nome = nomeInput?.value.trim();

        if (!nome) {
            SGTUtils.showToast('Nome é obrigatório', 'error');
            nomeInput?.focus();
            return;
        }

        const celular = this.form?.querySelector('input[name="celular"]')?.value;
        if (!celular) {
            SGTUtils.showToast('Celular é obrigatório', 'error');
            return;
        }

        // Loading state
        const originalText = this.btnSalvar.innerHTML;
        this.btnSalvar.disabled = true;
        this.btnSalvar.innerHTML = '<i class="bi bi-hourglass-split"></i> Salvando...';

        try {
            const formData = new FormData(this.form);

            const response = await fetch('salvar_cliente_ajax.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.handleSuccess(result);
            } else {
                throw new Error(result.error || 'Erro ao salvar cliente');
            }
        } catch (error) {
            console.error('Erro:', error);
            SGTUtils.showToast(error.message || 'Erro de conexão', 'error');
        } finally {
            this.btnSalvar.disabled = false;
            this.btnSalvar.innerHTML = originalText;
        }
    },

    handleSuccess(result) {
        const contato = result.nome.split(' ')[0] + ' - ' + (result.celular || '-');

        // Adiciona ao Select2
        const $select = $('#id_cliente');
        const newOption = new Option(result.nome, result.id, true, true);
        newOption.dataset.contato = contato;

        $select.append(newOption).trigger('change');

        // Preenche contato na obra
        document.getElementById('contato_obra').value = contato;

        this.close();
        SGTUtils.showToast('Cliente cadastrado com sucesso!', 'success');
    }
};

window.ClienteModal = ClienteModal;
