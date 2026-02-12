<?php
// validador_formato.php
declare(strict_types=1);

final class ValidadorBR {
    /**
     * Valida e formata um número de celular brasileiro.
     * Retorna o número limpo (apenas dígitos) ou false se inválido.
     */
    public static function validar(string $numero): string|false {
        // Remove tudo que não é dígito
        $limpo = preg_replace('/\D/', '', $numero);
        
        // Verifica se tem DDI 55 (se não tiver, adiciona para verificar)
        if (strlen($limpo) === 11) { // Celular sem 55 (11999999999)
            $limpo = '55' . $limpo;
        }
        
        // Regras BR: 55 + DDD (11-99) + 9 + 8 dígitos
        // Total: 13 dígitos
        if (!preg_match('/^55([1-9]{2})9[0-9]{8}$/', $limpo)) {
            return false;
        }
        
        // Lista de DDDs válidos
        $dddsValidos = ['11','12','13','14','15','16','17','18','19',
                       '21','22','24','27','28','31','32','33','34',
                       '35','37','38','41','42','43','44','45','46',
                       '47','48','49','51','53','54','55','61','62',
                       '63','64','65','66','67','68','69','71','73',
                       '74','75','77','79','81','82','83','84','85',
                       '86','87','88','89','91','92','93','94','95',
                       '96','97','98','99'];
        
        $ddd = substr($limpo, 2, 2);
        if (!in_array($ddd, $dddsValidos, true)) {
            return false;
        }
        
        return $limpo;
    }
}
