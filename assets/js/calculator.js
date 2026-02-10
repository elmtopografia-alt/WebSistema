/**
 * SGT Calculator - Módulo da calculadora
 */

const Calculator = {
    expression: '',
    display: null,
    modal: null,

    init() {
        this.display = document.getElementById('calcDisplay');
        this.modal = document.getElementById('calculatorModal');

        // Bind teclado
        document.addEventListener('keydown', (e) => this.handleKeydown(e));

        // Fechar ao clicar fora
        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) this.close();
            });
        }
    },

    open() {
        if (!this.modal) return;
        this.expression = '';
        this.updateDisplay('0');
        this.modal.classList.add('show');
    },

    close() {
        if (!this.modal) return;
        this.modal.classList.remove('show');
    },

    input(value) {
        if (this.expression === '0' && value !== '.') {
            this.expression = value;
        } else {
            this.expression += value;
        }
        this.updateDisplay(this.expression);
    },

    clear() {
        this.expression = '';
        this.updateDisplay('0');
    },

    backspace() {
        this.expression = this.expression.slice(0, -1);
        this.updateDisplay(this.expression || '0');
    },

    calculate() {
        try {
            // Substitui × e ÷ por operadores JS
            let expr = this.expression
                .replace(/×/g, '*')
                .replace(/÷/g, '/')
                .replace(/−/g, '-');

            // Avaliação segura (apenas matemática)
            const result = Function('"use strict"; return (' + expr + ')')();

            const formatted = Number.isInteger(result)
                ? result
                : parseFloat(result.toFixed(4));

            this.updateDisplay(formatted);
            this.expression = String(formatted);
        } catch (e) {
            this.updateDisplay('Erro');
            this.expression = '';
        }
    },

    updateDisplay(value) {
        if (this.display) {
            this.display.textContent = value;
        }
    },

    handleKeydown(e) {
        if (!this.modal?.classList.contains('show')) return;

        const key = e.key;

        if (/[0-9.]/.test(key)) this.input(key);
        else if (['+', '-', '*', '/'].includes(key)) this.input(key);
        else if (key === 'Enter' || key === '=') {
            e.preventDefault();
            this.calculate();
        }
        else if (key === 'Escape') this.close();
        else if (key === 'Backspace') this.backspace();
        else if (key === 'c' || key === 'C') this.clear();
    }
};

// Exporta globalmente
window.Calculator = Calculator;

// Funções globais para compatibilidade com onclick no HTML
window.calcInput = (val) => Calculator.input(val);
window.calcClear = () => Calculator.clear();
window.calcBackspace = () => Calculator.backspace();
window.calcEquals = () => Calculator.calculate();
