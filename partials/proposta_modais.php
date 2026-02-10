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
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 style="margin: 0; font-size: 1.125rem;"><i class="bi bi-person-plus-fill"></i> Novo Cliente</h5>
            <button type="button" class="btn btn-close-modal" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-novo-cliente">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="acao" value="criar_ajax">

                <div class="form-row cols-2">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome_cliente" class="form-control" required placeholder="Nome completo">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Empresa</label>
                        <input type="text" name="empresa" class="form-control" placeholder="Nome da empresa">
                    </div>
                    <div class="form-group">
                        <label class="form-label">CPF/CNPJ</label>
                        <input type="text" name="cnpj_cpf" class="form-control" placeholder="000.000.000-00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" placeholder="email@exemplo.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Celular *</label>
                        <input type="tel" name="celular" class="form-control" placeholder="(31) 99999-9999" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefone</label>
                        <input type="tel" name="telefone" class="form-control" placeholder="(31) 3333-3333">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline btn-close-modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="btn-salvar-cliente">
                <i class="bi bi-check-lg"></i> Salvar
            </button>
        </div>
    </div>
</div>
