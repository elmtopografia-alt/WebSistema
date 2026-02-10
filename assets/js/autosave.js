/**
 * SGT AutoSave - Salvamento automático de rascunho no localStorage
 */

const AutoSave = {
    key: 'sgt_proposta_draft',
    form: null,
    statusEl: null,
    saveTimeout: null,

    init() {
        this.form = document.getElementById('form-proposta');
        this.statusEl = document.getElementById('status-autosave');

        if (!this.form) return;

        // Verifica se é nova proposta
        this.checkNewProposal();

        this.bindEvents();
    },

    checkNewProposal() {
        // [MODIFICAÇÃO] Verifica flag injetada pelo PHP (mais robusto)
        if (typeof window.SGT_CLEAR_STORAGE !== 'undefined' && window.SGT_CLEAR_STORAGE === true) {
            console.log('🧹 Limpando rascunho (Nova Proposta solicitada)');
            this.clear();
            return;
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('nova') || urlParams.get('nova') === '1') {
            this.clear();
            // Remove parâmetro da URL sem recarregar
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
            return;
        }

        // Tenta restaurar rascunho existente
        this.restore();
    },

    bindEvents() {
        // Salva em qualquer alteração (com debounce)
        const inputs = this.form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('change', () => this.scheduleSave());
            input.addEventListener('input', () => this.scheduleSave());
        });

        // Limpa ao enviar
        this.form.addEventListener('submit', () => this.clear());

        // Salva antes de fechar a página
        window.addEventListener('beforeunload', () => {
            if (this.saveTimeout) this.save();
        });
    },

    scheduleSave() {
        if (this.saveTimeout) clearTimeout(this.saveTimeout);
        this.saveTimeout = setTimeout(() => this.save(), 1000);
        this.showStatus('Salvando...', 'saving');
    },

    save() {
        try {
            const data = SGTUtils.serializeForm(this.form);
            data._timestamp = Date.now();
            data._version = '2.0';

            localStorage.setItem(this.key, JSON.stringify(data));
            this.showStatus('Salvo', 'saved');
        } catch (e) {
            console.error('Erro ao salvar rascunho:', e);
            this.showStatus('Erro ao salvar', 'error');
        }
    },

    restore() {
        try {
            const draft = localStorage.getItem(this.key);
            if (!draft) return;

            const data = JSON.parse(draft);

            // Verifica versão e idade do rascunho
            if (data._version !== '2.0') {
                console.log('Versão de rascunho incompatível');
                return;
            }

            const age = Date.now() - (data._timestamp || 0);
            const maxAge = 7 * 24 * 60 * 60 * 1000; // 7 dias

            if (age > maxAge) {
                console.log('Rascunho expirado');
                this.clear();
                return;
            }

            // Restaura campos
            Object.keys(data).forEach(key => {
                if (key.startsWith('_')) return; // Ignora metadados

                const field = this.form.querySelector(`[name="${key}"]`);
                if (!field) return;

                if (field.type === 'checkbox') {
                    field.checked = data[key] === 'on' || data[key] === true;
                } else if (field.type === 'radio') {
                    const radio = this.form.querySelector(`[name="${key}"][value="${data[key]}"]`);
                    if (radio) radio.checked = true;
                } else {
                    field.value = data[key];
                    // Dispara evento change para Select2 e cálculos
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            SGTUtils.showToast(`Rascunho recuperado (${this.formatAge(age)})`, 'info');
        } catch (e) {
            console.error('Erro ao restaurar rascunho:', e);
        }
    },

    clear() {
        localStorage.removeItem(this.key);
        console.log('Rascunho limpo');
    },

    showStatus(text, type) {
        if (!this.statusEl) return;

        const icons = {
            saving: 'bi-cloud-arrow-up',
            saved: 'bi-cloud-check',
            error: 'bi-cloud-slash'
        };

        this.statusEl.innerHTML = `<i class="bi ${icons[type] || icons.saving}"></i> ${text}`;
        this.statusEl.style.display = 'inline-block';

        if (type === 'saved') {
            setTimeout(() => {
                this.statusEl.style.display = 'none';
            }, 2000);
        }
    },

    formatAge(ms) {
        const minutes = Math.floor(ms / 60000);
        const hours = Math.floor(ms / 3600000);
        const days = Math.floor(ms / 86400000);

        if (days > 0) return `${days}d atrás`;
        if (hours > 0) return `${hours}h atrás`;
        return `${minutes}min atrás`;
    }
};

window.AutoSave = AutoSave;
