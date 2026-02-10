/**
 * SGT Utils - Funções utilitárias compartilhadas
 */

const SGTUtils = {
    /**
     * Formata valor monetário para exibição
     */
    formatMoney(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value || 0);
    },

    /**
     * Converte string monetária para número
     */
    parseMoney(value) {
        if (!value) return 0;
        return parseFloat(value.toString().replace(/[R$\s.]/g, '').replace(',', '.')) || 0;
    },

    /**
     * Debounce para eventos frequentes
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Throttle para limitar execução
     */
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    /**
     * Mostra toast notification
     */
    showToast(message, type = 'info') {
        const icons = {
            success: 'check-circle-fill',
            error: 'exclamation-triangle-fill',
            warning: 'exclamation-circle-fill',
            info: 'info-circle-fill'
        };

        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="bi bi-${icons[type] || icons.info} text-${type === 'error' ? 'danger' : type}"></i>
            <span>${message}</span>
        `;

        container.appendChild(toast);
        
        // Auto-remove após 4 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    },

    /**
     * Validação de CPF/CNPJ básica
     */
    validaDocumento(doc) {
        doc = doc.replace(/\D/g, '');
        return doc.length === 11 || doc.length === 14;
    },

    /**
     * Máscara para telefone
     */
    maskPhone(value) {
        value = value.replace(/\D/g, '');
        if (value.length <= 10) {
            return value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        return value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    },

    /**
     * Serializa formulário para objeto
     */
    serializeForm(form) {
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                if (!Array.isArray(data[key])) {
                    data[key] = [data[key]];
                }
                data[key].push(value);
            } else {
                data[key] = value;
            }
        }
        return data;
    },

    /**
     * Verifica se é dispositivo touch
     */
    isTouchDevice() {
        return window.matchMedia('(pointer: coarse)').matches;
    },

    /**
     * Animação de scroll suave
     */
    scrollTo(element, offset = 0) {
        const top = element.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    }
};

// Exporta para uso global
window.SGTUtils = SGTUtils;
