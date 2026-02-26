<?php
/**
 * Mapeamento de Equipamentos para Propostas Técnicas
 * Resolve: Exibição genérica → Específica e factual
 */

class EquipamentosMapper {
    
    // Tabela de relacionamento conforme solicitado
    private static $mapeamento = [
        'veiculo' => [
            'campos_bd' => ['marca_veiculo', 'modelo_veiculo'],
            'template_var' => '${Veiculo}',
            'descricao' => 'Veículo de Apoio',
            'categoria' => 'Transporte/Logística'
        ],
        'estacao_total' => [
            'campos_bd' => ['marca_estacao_total', 'modelo_estacao_total'],
            'template_var' => '${Estacao_Total}',
            'descricao' => 'Estação Total',
            'categoria' => 'Medição de Precisão'
        ],
        'gps' => [
            'campos_bd' => ['marca_gps', 'modelo_gps'],
            'template_var' => '${GPS}',
            'descricao' => 'Receptor GNSS/GPS',
            'categoria' => 'Georreferenciamento'
        ],
        'drone' => [
            'campos_bd' => ['marca_drone', 'modelo_drone'],
            'template_var' => '${Drone}',
            'descricao' => 'Drone de Mapeamento',
            'categoria' => 'Sensoriamento Remoto'
        ]
    ];

    /**
     * Processa dados brutos do BD e retorna array estruturado
     * 
     * @param array $dados_bd - Linha do banco de dados (fetch_assoc)
     * @return array - Equipamentos formatados para exibição
     */
    public static function processar($dados_bd) {
        $equipamentos = [];
        
        foreach (self::$mapeamento as $tipo => $config) {
            $marca = isset($dados_bd[$config['campos_bd'][0]]) ? trim($dados_bd[$config['campos_bd'][0]]) : '';
            $modelo = isset($dados_bd[$config['campos_bd'][1]]) ? trim($dados_bd[$config['campos_bd'][1]]) : '';
            
            // Lógica de concatenação: Marca + " " + Modelo
            $especificacao_completa = '';
            if (!empty($marca) && !empty($modelo)) {
                $especificacao_completa = $marca . ' ' . $modelo;
            } elseif (!empty($marca)) {
                $especificacao_completa = $marca;
            } elseif (!empty($modelo)) {
                $especificacao_completa = $modelo;
            } else {
                $especificacao_completa = 'Não especificado';
            }
            
            $equipamentos[$tipo] = [
                'categoria' => $config['categoria'],
                'descricao' => $config['descricao'],
                'especificacao' => $especificacao_completa,
                'marca' => $marca,
                'modelo' => $modelo,
                'template_var' => $config['template_var']
            ];
        }
        
        return $equipamentos;
    }

    /**
     * Substitui variáveis no template pelo conteúdo real
     * 
     * @param string $conteudo - HTML/Texto com variáveis ${X}
     * @param array $equipamentos_processados - Retorno de processar()
     * @return string - Conteúdo com valores reais
     */
    public static function aplicarNoTemplate($conteudo, $equipamentos_processados) {
        $substituicoes = [];
        
        foreach ($equipamentos_processados as $tipo => $dados) {
            $substituicoes[$dados['template_var']] = $dados['especificacao'];
        }
        
        // Substituições adicionais para fallback
        $substituicoes['${Veiculo}'] = $equipamentos_processados['veiculo']['especificacao'];
        $substituicoes['${Estacao_Total}'] = $equipamentos_processados['estacao_total']['especificacao'];
        $substituicoes['${GPS}'] = $equipamentos_processados['gps']['especificacao'];
        $substituicoes['${Drone}'] = $equipamentos_processados['drone']['especificacao'];
        
        return strtr($conteudo, $substituicoes);
    }

    /**
     * Gera HTML da tabela de equipamentos para o bloco 6
     * Versão "Básica" - Apenas Equipamento e Modelo
     */
    public static function gerarTabelaHTML($equipamentos_processados) {
        $html = '<table class="tabela-equipamentos-licitacao">';
        $html .= '<thead>
                    <tr>
                        <th>Equipamento</th>
                        <th>Modelo / Especificação</th>
                    </tr>
                  </thead>';
        $html .= '<tbody>';
        
        // Ordem estratégica: do mais impressionante para o menos
        $ordem_exibicao = ['drone', 'estacao_total', 'gps', 'veiculo'];
        
        foreach ($ordem_exibicao as $tipo) {
            if (!isset($equipamentos_processados[$tipo])) continue;
            
            $eq = $equipamentos_processados[$tipo];
            
            // Só exibe se tiver especificação real (não "Não especificado")
            if ($eq['especificacao'] === 'Não especificado') continue;
            
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($eq['descricao']) . '</td>';
            $html .= '<td class="destaque-equipamento">' . htmlspecialchars($eq['especificacao']) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        
        return $html;
    }


    /**
     * Validação: Verifica se todos os equipamentos críticos estão preenchidos
     * Útil para alertar antes de gerar proposta incompleta
     */
    public static function validarCompletude($dados_bd) {
        $faltantes = [];
        $processados = self::processar($dados_bd);
        
        foreach ($processados as $tipo => $dados) {
            if ($dados['especificacao'] === 'Não especificado') {
                $faltantes[] = $dados['descricao'];
            }
        }
        
        return [
            'completo' => empty($faltantes),
            'faltantes' => $faltantes,
            'percentual' => (4 - count($faltantes)) / 4 * 100
        ];
    }
}
?>
