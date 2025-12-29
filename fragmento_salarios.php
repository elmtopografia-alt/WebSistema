Warning: Invalid argument supplied for foreach() in /home/storage/3/27/cf/elmtopografia2/public_html/Orcamento/fragmento_salarios.php on line 26
Função	Qtd	Salário Base (Mensal)	Enc%	Dias	Subtotal	
Subtotal Equipe: R$ 0,00<?php
/**
 * FRAGMENTO: EQUIPE E SALÁRIOS (VERSÃO FINAL DE ENGENHARIA)
 * FÓRMULA VALIDADA: (Base * 1.67 / 30) * Qtd * Dias
 */

// 1. CONEXÃO E SEGURANÇA
if (!isset($conn)) {
    require_once 'config.php';
    require_once 'db.php';
    $conn = (isset($_SESSION['ambiente']) && $_SESSION['ambiente'] === 'demo') ? Database::getDemo() : Database::getProd();
}
$id_proposta_alvo = $_GET['id'] ?? 59;

// 2. BUSCA DE DADOS
$tipos_funcao = $conn->query("SELECT * FROM Tipo_Funcoes ORDER BY nome ASC")->fetch_all(MYSQLI_ASSOC);
$res_itens = $conn->query("SELECT * FROM Proposta_Salarios WHERE id_proposta = $id_proposta_alvo");

// 3. PROTEÇÃO CONTRA FOREACH VAZIO
// Se o banco não trouxer nada, criamos 1 linha em branco manual para evitar o erro
$itens_salarios = ($res_itens && $res_itens->num_rows > 0) ? $res_itens->fetch_all(MYSQLI_ASSOC) : [null];

$total_secao_php = 0;
?>

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 30%;">Função</th>
                <th>Qtd</th>
                <th>Salário Base (Mensal)</th>
                <th>Enc%</th>
                <th>Dias</th>
                <th style="width: 150px;">Subtotal</th>
                <th style="width: 40px;"></th>
            </tr>
        </thead>
        <tbody id="tbody-salarios">
            <?php foreach ($itens_salarios as $it): 
                // Se 'it' for null (banco vazio), definimos valores padrão
                $id_f  = $it['id_funcao'] ?? '';
                $v_qtd = $it['quantidade'] ?? 1;
                $v_base = $it['salario_base'] ?? 0;
                $v_dias = $it['dias_trabalhados'] ?? 1;
                $p_enc  = 1.67;

                // FÓRMULA: (Base * 1.67 / 30) * Qtd * Dias
                $subtotal_linha = ($v_base > 0) ? (($v_base * $p_enc) / 30) * $v_qtd * $v_dias : 0;
                $total_secao_php += $subtotal_linha;
            ?>
            <tr>
                <td>
                    <select name="salario_id_funcao[]" class="form-select form-select-sm">
                        <option value="">Selecione...</option>
                        <?php foreach($tipos_funcao as $f): ?>
                            <option value="<?= $f['id_funcao'] ?>" 
                                    data-valor="<?= $f['salario_base_default'] ?>" 
                                    <?= ($f['id_funcao'] == $id_f) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" name="salario_qtd[]" class="form-control form-control-sm recalc text-center" value="<?= $v_qtd ?>"></td>
                <td><input type="number" name="salario_valor[]" class="form-control form-control-sm recalc text-end" value="<?= $v_base ?>" step="0.01"></td>
                <td><input type="number" name="encargos[]" class="form-control form-control-sm recalc text-center" value="67" readonly style="background-color: #eee;"></td>
                <td><input type="number" name="salario_dias[]" class="form-control form-control-sm recalc text-center" value="<?= $v_dias ?>"></td>
                
                <td class="total-linha text-end fw-bold text-primary" style="background-color: #f0f7ff;">
                    R$ <?= number_format($subtotal_linha, 2, ',', '.') ?>
                </td>
                
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row">×</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center border-top pt-2">
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-salario">+ Adicionar Função</button>
    <div>
        <span class="text-muted small">Subtotal Equipe:</span>
        <span id="total-secao-salarios" class="fw-bold fs-5 ms-2">
            R$ <?= number_format($total_secao_php, 2, ',', '.') ?>
        </span>
    </div>
</div>