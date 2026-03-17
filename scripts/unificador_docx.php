<?php
/**
 * UNIFICADOR DE MODELOS DOCX - SGT PROPOSTAS
 * Versão 2.0 - Evita substituições duplicadas (Recursão)
 */

class UnificadorDocx {
    // Ordem importa: Mais específicos primeiro
    private $mapa = [
        'nome_cliente_salvo' => 'nome_cliente',
        'email_salvo'        => 'email_cliente',
        'whatsapp_salvo'     => 'whatsapp_cliente',
        'telefone_salvo'     => 'telefone_cliente',
        'celular_salvo'      => 'celular_cliente',
        'contato_obra'       => 'nome_contato',
        'valor_final_proposta' => 'valor_total',
        'valorproposta'      => 'valor_total',
        'ValorProposta'      => 'valor_total',
        'valorextenso'       => 'valor_extenso',
        'ValorExtenso'       => 'valor_extenso',
        'mobilizacao_valor'  => 'valor_entrada',
        'restante_valor'     => 'valor_restante',
        'DExrenso'           => 'data_extenso',
        'DataExtenso'        => 'data_extenso',
        'numero_proposta'    => 'numero_proposta',
        'endereco_obra'      => 'endereco_obra',
        'cidade_obra'        => 'cidade_obra',
        'bairro_obra'        => 'bairro_obra',
        'estado_obra'        => 'estado_obra',
        'finalidade'         => 'finalidade',
        'chave_pix'          => 'chave_pix',
        'PIX'                => 'chave_pix',
        'Empresa'            => 'empresa',
        'CNPJ'               => 'cnpj',
        
        // Limpeza de duplicados curtos
        'mail'               => 'email_cliente',
        'e-mail'             => 'email_cliente',
        'whatsapp'           => 'whatsapp_cliente',
        'contato'            => 'nome_contato'
    ];

    public function processar($arquivoOrigem, $arquivoDestino) {
        if (!file_exists($arquivoOrigem)) return false;
        copy($arquivoOrigem, $arquivoDestino);

        $zip = new ZipArchive();
        if ($zip->open($arquivoDestino) === TRUE) {
            $content = $zip->getFromName('word/document.xml');
            
            // 1. Limpa todas as chaves existentes (remove ${ e }) para trabalhar apenas com o texto base
            // Isso evita ${${tag}}
            foreach ($this->mapa as $antigo => $novo) {
                 $content = str_replace(['${' . $antigo . '}', $antigo], "##TEMP_" . $antigo . "##", $content);
            }

            // 2. Substitui os placeholders temporários pelas tags finais do contrato
            foreach ($this->mapa as $antigo => $novo) {
                $content = str_replace("##TEMP_" . $antigo . "##", '${' . $novo . '}', $content);
            }

            // 3. Limpeza final de possíveis tags quebradas (suporte básico)
            $content = str_replace(['${${', '}}'], ['${', '}'], $content);

            $zip->addFromString('word/document.xml', $content);
            $zip->close();
            return true;
        }
        return false;
    }
}

// Execução (apenas se chamado diretamente via CLI)
if (isset($argv[0]) && basename($argv[0]) == 'unificador_docx.php') {
    $origem = $argv[1] ?? null;
    $destino = $argv[2] ?? null;

    if (!$origem || !$destino) {
        die("Uso: php unificador_docx.php [arquivo_origem] [arquivo_destino]\n");
    }

    $unificador = new UnificadorDocx();
    if ($unificador->processar($origem, $destino)) {
        echo "✓ Arquivo unificado com sucesso: $destino\n";
    } else {
        echo "✗ Erro ao processar arquivo.\n";
    }
}
