/**
 * SGT Wizard - Navegação do formulário multi-etapas
 */

const Wizard = {
    current: 1,
    total: 4,
    container: null,
    content: null,

    init() {
        this.container = document.querySelector('.wizard-wrapper');
        this.content = document.querySelector('.wizard-content');

        this.bindEvents();
        this.initSelect2();
        this.initTabs();
        this.updateUI();

        // Trigger change se serviço pré-selecionado
        const servicoSelect = document.getElementById('id_servico');
        if (servicoSelect?.value) {
            this.handleServicoChange({ target: servicoSelect });
        }
    },

    bindEvents() {
        // Botões de navegação
        document.getElementById('btn-next')?.addEventListener('click', () => this.next());
        document.getElementById('btn-prev')?.addEventListener('click', () => this.prev());

        // Swipe support para mobile
        let touchStartX = 0;
        this.content?.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        this.content?.addEventListener('touchend', (e) => {
            const diff = touchStartX - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 50) {
                diff > 0 ? this.next() : this.prev();
            }
        }, { passive: true });

        // Auto-fill cliente
        $('#id_cliente')?.on('select2:select', (e) => {
            const contato = e.params.data.element?.dataset.contato || '';
            document.getElementById('contato_obra').value = contato;
        });

        // Auto-fill serviço
        document.getElementById('id_servico')?.addEventListener('change',
            (e) => this.handleServicoChange(e)
        );
    },

    handleServicoChange(e) {
        const select = e.target;
        const option = select.options[select.selectedIndex];
        if (!option) return;

        const descricao = option.dataset.descricao;
        const finalidade = document.getElementById('finalidade');
        const tipoLevantamento = document.getElementById('tipo_levantamento');

        if (descricao && finalidade) {
            finalidade.value = descricao;
        }

        if (tipoLevantamento) {
            tipoLevantamento.value = 'Levantamento ' + option.text;
        }
    },

    initSelect2() {
        const isMobile = SGTUtils.isTouchDevice();
        const clienteSelect = $('#id_cliente');

        if (!clienteSelect.length) return;

        // Destroi se já existir
        if (clienteSelect.hasClass('select2-hidden-accessible')) {
            clienteSelect.select2('destroy');
        }

        clienteSelect.select2({
            placeholder: 'Buscar cliente...',
            allowClear: true,
            theme: 'default',
            dropdownParent: $('body'),
            minimumResultsForSearch: 5,
            selectOnClose: !isMobile,
            language: {
                noResults: () => 'Nenhum cliente encontrado',
                searching: () => 'Buscando...'
            }
        });
    },

    initTabs() {
        document.querySelectorAll('.cost-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.dataset.tab;

                // Atualiza tabs
                document.querySelectorAll('.cost-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                // Atualiza panels
                document.querySelectorAll('.cost-panel').forEach(p => p.classList.add('hidden'));
                document.getElementById(`panel-${targetTab}`)?.classList.remove('hidden');
            });
        });
    },

    validate() {
        const validations = {
            1: () => {
                const cliente = document.getElementById('id_cliente')?.value;
                const endereco = document.querySelector('input[name="endereco"]')?.value;

                if (!cliente) {
                    SGTUtils.showToast('Selecione um cliente', 'error');
                    $('#id_cliente').select2('open');
                    return false;
                }
                if (!endereco) {
                    SGTUtils.showToast('Informe o endereço da obra', 'error');
                    document.querySelector('input[name="endereco"]')?.focus();
                    return false;
                }
                return true;
            },
            2: () => {
                const servico = document.getElementById('id_servico')?.value;
                if (!servico) {
                    SGTUtils.showToast('Selecione o tipo de serviço', 'error');
                    document.getElementById('id_servico')?.focus();
                    return false;
                }
                return true;
            },
            3: () => true,
            4: () => true
        };

        return validations[this.current]?.() ?? true;
    },

    next() {
        if (this.current < this.total && this.validate()) {
            this.current++;
            this.updateUI();
        }
    },

    prev() {
        if (this.current > 1) {
            this.current--;
            this.updateUI();
        }
    },

    goTo(step) {
        if (step >= 1 && step <= this.total && step !== this.current) {
            if (step > this.current && !this.validate()) return;
            this.current = step;
            this.updateUI();
        }
    },

    updateUI() {
        // Atualiza panels
        document.querySelectorAll('.step-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        document.getElementById(`step-${this.current}`)?.classList.add('active');

        // Atualiza steps indicator
        document.querySelectorAll('.step').forEach((step, index) => {
            step.classList.remove('active', 'completed');
            const stepNum = index + 1;

            if (stepNum < this.current) {
                step.classList.add('completed');
            } else if (stepNum === this.current) {
                step.classList.add('active');
            }
        });

        // Atualiza botões
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const btnLegacy = document.getElementById('btn-legacy');
        const btnFinish = document.getElementById('btn-finish');

        if (btnPrev) btnPrev.classList.toggle('hidden', this.current === 1);
        if (btnNext) btnNext.classList.toggle('hidden', this.current === this.total);
        if (btnLegacy) btnLegacy.classList.toggle('hidden', this.current !== this.total);
        if (btnFinish) btnFinish.classList.toggle('hidden', this.current !== this.total);

        // Scroll to top
        if (this.content) {
            this.content.scrollTop = 0;
        }

        // Acessibilidade - foco no título
        const currentPanel = document.getElementById(`step-${this.current}`);
        const title = currentPanel?.querySelector('.section-title');
        if (title) {
            title.setAttribute('tabindex', '-1');
            title.focus();
        }
    }
};

window.Wizard = Wizard;
