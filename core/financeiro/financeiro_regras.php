<?php
/**
 * financeiro_regras.php
 * Regras de negócio: validações, permissões e status.
 * Regra: Sem HTML, sem banco direto (recebe dados).
 */

class FinanceiroRegras {

    const PLANO_BASICO_ID = 1;
    const VALOR_MINIMO_PLANO = 29.90;

    /**
     * Verifica se o usuário pode contratar um plano.
     */
    public static function podeContratar(array $usuario): bool {
        // Exemplo: Não pode contratar se estiver suspenso
        if (isset($usuario['status']) && $usuario['status'] === 'suspenso') {
            return false;
        }
        return true;
    }

    /**
     * Define o status financeiro com base nos pagamentos.
     */
    public static function definirStatusFinanceiro(array $faturasPendentes): string {
        if (empty($faturasPendentes)) {
            return 'em_dia';
        }

        $hoje = new DateTime();
        foreach ($faturasPendentes as $fatura) {
            $vencimento = new DateTime($fatura['data_vencimento']);
            if ($hoje > $vencimento) {
                return 'inadimplente';
            }
        }

        return 'pendente'; // Tem fatura aberta mas não venceu
    }

    /**
     * Valida se o valor do pagamento é aceitável.
     */
    public static function validarValorPagamento(float $valorPago, float $valorEsperado): bool {
        // Aceita se pagar pelo menos 99% do valor (margem de erro de arredondamento)
        return $valorPago >= ($valorEsperado * 0.99);
    }
}
?>
