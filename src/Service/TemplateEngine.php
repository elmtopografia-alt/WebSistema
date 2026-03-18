<?php
/**
 * MOTOR DE TEMPLATE - PADRÃO ÚNICO v3.0
 * Implementa validação estrita: Chave fora do padrão = Exceção Crítica.
 */

declare(strict_types=1);

namespace SGT\Service;

use Exception;

class TemplateEngine
{
    /**
     * Lista Única e Absoluta de Chaves Permitidas (Contrato v3.0)
     */
    private array $chavesPermitidas = [
        // Dados do Cliente
        'nome_cliente', 'email_cliente', 'whatsapp_cliente', 'telefone_cliente',
        
        // Dados da Empresa (Proponente)
        'empresa_nome', 'empresa_cnpj', 'empresa_logo_url', 'empresa_cidade',
        
        // Proposta e Obra
        'numero_proposta', 'data_emissao', 'data_emissao_extenso',
        'endereco_obra', 'cidade_obra', 'estado_obra', 'finalidade_obra',
        
        // Valores Financeiros
        'valor_total', 'valor_total_extenso', 'valor_entrada', 'valor_restante',
        
        // Dados Bancários
        'banco_nome', 'banco_agencia', 'banco_conta'
    ];
    
    /**
     * Renderiza o conteúdo substituindo as chaves pelos dados.
     * 
     * @param string $conteudo O texto (HTML ou XML) com as tags ${chave}
     * @param array $dados Dicionário de dados vindos do banco
     * @return string
     * @throws Exception Caso encontre chaves inválidas ou dados ausentes
     */
    public function renderizar(string $conteudo, array $dados): string 
    {
        // 1. Extrair todas as chaves presentes no template
        preg_match_all('/\$\{(\w+)\}/', $conteudo, $matches);
        $chavesNoTemplate = array_unique($matches[1]);
        
        // 2. Validação Estrita (Estado Saudável)
        foreach ($chavesNoTemplate as $chave) {
            // Verifica se a chave existe na "Norma v3.0"
            if (!in_array($chave, $this->chavesPermitidas)) {
                throw new Exception(
                    "ESTADO DOENTIO: Chave inválida '\${{$chave}}' detectada no modelo. " .
                    "Apenas chaves do Padrão Único v3.0 são aceitas. Edite o DOCX."
                );
            }
            
            // Verifica se o dado foi fornecido pelo sistema
            if (!isset($dados[$chave])) {
                // Se a chave é permitida mas o dado não veio, é um erro de integração/banco
                throw new Exception(
                    "DADO AUSENTE: A chave '\${{$chave}}' é válida, mas não há dados correspondentes no banco. " .
                    "Verifique se a coluna '{$chave}' existe e está preenchida."
                );
            }
        }
        
        // 3. Substituição Massiva (Executa apenas se passou na validação acima)
        $resultado = $conteudo;
        foreach ($chavesNoTemplate as $chave) {
            $valor = (string)($dados[$chave] ?? '');
            $resultado = str_replace("\${{$chave}}", $valor, $resultado);
        }
        
        return $resultado;
    }

    /**
     * Retorna a lista de chaves permitidas para referência da UI
     */
    public function getChavesPermitidas(): array 
    {
        return $this->chavesPermitidas;
    }
}
