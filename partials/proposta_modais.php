<!-- CALCULATOR MODAL -->
<div class="calculator-modal" id="calculatorModal">
    <div class="calculator-box">
        <div class="calculator-header">
            <h5><i class="bi bi-calculator"></i> Calculadora</h5>
            <button type="button" class="calculator-close" onclick="closeCalculator()">&times;</button>
        </div>
        <div class="calculator-display" id="calcDisplay">0</div>
        <div class="calculator-buttons">
            <button type="button" class="calc-btn calc-btn-clear" onclick="calcClear()">C</button>
            <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('(')">(</button>
            <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput(')')">)</button>
            <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('/')">÷</button>

            <button type="button" class="calc-btn" onclick="calcInput('7')">7</button>
            <button type="button" class="calc-btn" onclick="calcInput('8')">8</button>
            <button type="button" class="calc-btn" onclick="calcInput('9')">9</button>
            <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('*')">×</button>

            <button type="button" class="calc-btn" onclick="calcInput('4')">4</button>
            <button type="button" class="calc-btn" onclick="calcInput('5')">5</button>
            <button type="button" class="calc-btn" onclick="calcInput('6')">6</button>
            <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('-')">−</button>

            <button type="button" class="calc-btn" onclick="calcInput('1')">1</button>
            <button type="button" class="calc-btn" onclick="calcInput('2')">2</button>
            <button type="button" class="calc-btn" onclick="calcInput('3')">3</button>
            <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('+')">+</button>

            <button type="button" class="calc-btn" onclick="calcInput('0')">0</button>
            <button type="button" class="calc-btn" onclick="calcInput('.')">.</button>
            <button type="button" class="calc-btn" onclick="calcBackspace()">⌫</button>
            <button type="button" class="calc-btn calc-btn-equals" onclick="calcEquals()">=</button>
        </div>
    </div>
</div>

<!-- MODAL NOVO CLIENTE -->
<div class="modal" id="modalNovoCliente">
    <div class="modal-dialog glass">
        <div class="modal-header" style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(59, 130, 246, 0.1)); border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h5 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: #fff;">
                <i class="bi bi-person-plus-fill text-orange-400"></i> Novo Cliente
            </h5>
            <button type="button" class="btn-close-modal" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">&times;</button>
        </div>
        <div class="modal-body" style="padding: 1.5rem;">
            <form id="form-novo-cliente">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="acao" value="criar_ajax">

                <div class="form-row cols-2">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label" style="color: #94a3b8;">Nome completo *</label>
                        <input type="text" name="nome_cliente" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" required placeholder="Digite o nome completo">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label" style="color: #94a3b8;">Empresa (Opcional)</label>
                        <input type="text" name="empresa" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="Nome da empresa">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #94a3b8;">CPF/CNPJ</label>
                        <input type="text" name="cnpj_cpf" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="000.000.000-00">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #94a3b8;">E-mail</label>
                        <input type="email" name="email" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="email@exemplo.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #94a3b8;">Celular *</label>
                        <input type="tel" name="celular" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="(31) 99999-9999" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #94a3b8;">Telefone fixo</label>
                        <input type="tel" name="telefone" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="(31) 3333-3333">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer" style="padding: 1.25rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="btn btn-outline btn-close-modal" style="border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; background: transparent;">Cancelar</button>
            <button type="button" class="btn btn-success" id="btn-salvar-cliente" style="background: #10b981; border: none; font-weight: 600;">
                <i class="bi bi-check-lg"></i> Salvar Cliente
            </button>
        </div>
    </div>
</div>
