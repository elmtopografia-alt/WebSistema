<!-- STEP 4: FECHAMENTO -->
<div class="step-panel" id="step-4">
    <div class="step-actions">
        <a href="painel.php" class="btn-action" title="Voltar ao Painel">
            <i class="bi bi-house-door"></i> Painel
        </a>
        <button type="button" class="btn-action btn-action-calc" onclick="openCalculator()" title="Abrir Calculadora">
            <i class="bi bi-calculator"></i> Calculadora
        </button>
    </div>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; line-height: 1.2;">Fechamento da Proposta</h1>
    <p style="color: #64748b; font-size: 0.875rem; margin-bottom: 1.5rem;">Revise os valores e defina as condições comerciais.</p>

    <div class="closing-section">

        <!-- Top Section: Cost Summary vs Profit (45% / 55%) -->
        <div class="step-4-grid-top">
            <!-- Left: Cost Summary Box (45%) -->
            <div class="cost-summary-box">
                <div class="cost-summary-title">Resumo de Custos</div>
                <!-- Cost Details -->
                <div class="cost-summary-details">
                    <div class="cost-summary-row">
                        <span>Equipe:</span>
                        <span id="resumo-salarios-display">R$ 0,00</span>
                    </div>
                    <div class="cost-summary-row">
                        <span>Estadia:</span>
                        <span id="resumo-estadia-display">R$ 0,00</span>
                    </div>
                    <div class="cost-summary-row">
                        <span>Combustível:</span>
                        <span id="resumo-consumos-display">R$ 0,00</span>
                    </div>
                    <div class="cost-summary-row">
                        <span>Equipamentos:</span>
                        <span id="resumo-locacao-display">R$ 0,00</span>
                    </div>
                    <div class="cost-summary-row">
                        <span>Administrativo:</span>
                        <span id="resumo-admin-display">R$ 0,00</span>
                    </div>
                </div>
                <!-- Hidden fields for calculation (always hidden inside box for structure) -->
                <div style="display: none !important; visibility: hidden; position: absolute; left: -9999px;">
                    <strong id="resumo-salarios">0</strong>
                    <strong id="resumo-estadia">0</strong>
                    <strong id="resumo-consumos">0</strong>
                    <strong id="resumo-locacao">0</strong>
                    <strong id="resumo-admin">0</strong>
                </div>
                <div class="cost-summary-total-row">
                    <span>Total Custos:</span>
                    <span class="text-red-bold" id="total-custos-geral">R$ 0,00</span>
                </div>
            </div>

            <!-- Right: Margin & Discount (55%) -->
            <div class="margin-section">
                <!-- Margem de Lucro -->
                <div>
                    <label class="label-padrao">Margem de Lucro (%)</label>
                    <div class="margin-input-wrapper">
                        <input type="number" name="percentual_lucro" id="percentual_lucro" class="form-control" value="30" step="0.1" min="0" inputmode="decimal">
                        <span class="percent-label">%</span>
                    </div>
                    <div class="margin-value-display" id="valor-lucro">+ R$ 0,00</div>
                </div>

                <!-- Desconto -->
                <div>
                    <label class="label-padrao">Desconto (R$)</label>
                    <input type="number" name="valor_desconto" id="valor_desconto" class="form-control" value="0" step="0.01" min="0" inputmode="decimal">
                </div>
            </div>
        </div>

        <div class="divider-horizontal"></div>

        <!-- Bottom Section: Payment vs Final Value (55% / 45%) -->
        <div class="step-4-grid-bottom">
            <!-- Left: Condições de Pagamento (55%) -->
            <div>
                <div class="payment-conditions-title">Condições de Pagamento</div>
                <div class="payment-grid">
                    <!-- Row 1: Entrada -->
                    <div class="payment-input-group">
                        <label class="label-padrao">Entrada %</label>
                        <input type="number" name="mobilizacao_percentual" id="mobilizacao_percentual" class="form-control" value="30" min="0" max="100" inputmode="numeric">
                    </div>
                    <div class="payment-input-group">
                        <label class="label-padrao">Valor Entrada</label>
                        <input type="text" id="mobilizacao_valor_display" class="form-control input-readonly" readonly value="R$ 0,00">
                    </div>
                    <!-- Row 2: Restante -->
                    <div class="payment-input-group">
                        <label class="label-padrao">Restante %</label>
                        <div class="restante-input-group">
                            <input type="text" id="restante_percentual_display" class="form-control input-readonly" value="70" readonly>
                            <span class="percent-text">%</span>
                        </div>
                    </div>
                    <div class="payment-input-group">
                        <label class="label-padrao">Valor Restante</label>
                        <input type="text" id="restante_valor_display" class="form-control input-readonly" readonly value="R$ 0,00">
                    </div>
                </div>
            </div>

            <!-- Right: Final Value Display (45%) -->
            <div class="final-value-container">
                <span class="big-total-label">VALOR FINAL</span>
                <span class="big-total-value" id="valor-final-proposta">R$ 0,00</span>
            </div>
        </div>
    </div>
</div>
