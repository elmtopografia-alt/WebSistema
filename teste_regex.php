<?php
// TESTE ISOLADO DO REGEX - NÃO PRECISA RODAR O SISTEMA INTEIRO

// Simula o que vem da IA
$dados['cronograma_content'] = '
Etapa 1: Levantamento topográfico
Etapa 2: Processamento de dados

R$ 3.500,00 (TRÊS MIL E QUINHENTOS REAIS)

Este investimento traduz-se em produtividade multiplicada: milhões de coordenadas georreferenciadas coletadas em horas, não em dias, eliminando gargalos operacionais e acelerando entregas sem comprometer a precisão.

Etapa 3: Entrega final
';

// ========== CÓDIGO QUE VOCÊ VAI COPIAR PARA O SISTEMA ==========
if (!empty($dados['cronograma_content'])) {
    $cronoTexto = $dados['cronograma_content'];
    
    // Remove valor monetário apenas se for positivo ou zero
    $cronoTexto = preg_replace_callback(
        '/R\$\s*([\d\.,]+)(?:\s*\([^)]+\))?/iu',
        function($matches) {
            $valorLimpo = str_replace('.', '', $matches[1]);
            $valorLimpo = str_replace(',', '.', $valorLimpo);
            $valor = (float) $valorLimpo;
            
            if ($valor >= 0) {
                return '[REMOVIDO:' . $matches[0] . ']'; // Visualização do que foi removido
            }
            return $matches[0];
        },
        $cronoTexto
    );
    
    $cronoTexto = preg_replace("/\n\s*\n+/", "\n", $cronoTexto);
    $cronoTexto = trim($cronoTexto);
}
// ==============================================================

echo "<pre>";
echo "========== RESULTADO ==========\n\n";
echo $cronoTexto;
echo "\n\n========== FIM ==========";