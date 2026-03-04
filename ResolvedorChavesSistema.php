<?php
/**
 * RESOLVEDOR DE CHAVES DO SISTEMA v3.1 (Restaurado)
 * Resolve variáveis mágicas para os modelos de propostas.
 */

class ResolvedorChavesSistema 
{
    private $conn;

    public function __construct($conexaoDB) 
    {
        $this->conn = $conexaoDB;
    }

    public function resolver(array $chavesNecessarias, int $id_usuario, array $dadosExtras = []): array 
    {
        $resolvidas = [];
        $empresa = $this->buscarDadosEmpresa($id_usuario);
        
        $d = $dadosExtras;

        foreach ($chavesNecessarias as $chave) {
            switch ($chave) {
                // EMPRESA
                case 'Empresa':
                case 'empresa':
                case 'nome_empresa':
                    $resolvidas[$chave] = $empresa['Empresa'] ?? 'SGT Topografia';
                    break;
                case 'CNPJ':
                case 'cnpj':
                    $resolvidas[$chave] = $empresa['CNPJ'] ?? '';
                    break;
                case 'whatsapp':
                    $resolvidas[$chave] = $empresa['WhatsApp'] ?? $empresa['Telefone'] ?? '';
                    break;
                case 'Cidade':
                case 'cidade_empresa':
                    $resolvidas[$chave] = $empresa['Cidade'] ?? 'Belo Horizonte';
                    break;
                case 'logo_empresa':
                case 'logomarca':
                case 'logo':
                    // Para HTML, se estivermos no ModeloBase, as imagens podem ser injetadas
                    // mas ModeloBase usa htmlspecialchars. 
                    // No entanto, podemos retornar o URL da imagem aqui.
                    $resolvidas[$chave] = $empresa['logo_url'] ?? 'assets/logo_sgt.png';
                    break;
                
                // CLIENTE - CORREÇÃO: Priorizar $dadosExtras
                case 'nome_cliente_salvo':
                case 'nome_cliente':
                    $resolvidas[$chave] = $d['nome_cliente_salvo'] ?? $d['nome_cliente'] ?? 'Cliente não informado';
                    break;
                case 'email_salvo':
                case 'email_cliente':
                    $resolvidas[$chave] = $d['email_salvo'] ?? $d['email_cliente'] ?? '';
                    break;
                case 'telefone_salvo':
                case 'telefone_cliente':
                    $resolvidas[$chave] = $d['telefone_salvo'] ?? $d['telefone_cliente'] ?? '';
                    break;
                case 'celular_salvo':
                case 'celular_cliente':
                    $resolvidas[$chave] = $d['celular_salvo'] ?? $d['celular_cliente'] ?? '';
                    break;
                case 'whatsapp_salvo':
                case 'whatsapp_cliente':
                    $resolvidas[$chave] = $d['whatsapp_salvo'] ?? $d['whatsapp_cliente'] ?? '';
                    break;
                
                // OBRA - CORREÇÃO: Priorizar $dadosExtras
                case 'endereco_obra':
                    $resolvidas[$chave] = $d['endereco_obra'] ?? '';
                    break;
                case 'bairro_obra':
                    $resolvidas[$chave] = $d['bairro_obra'] ?? '';
                    break;
                case 'cidade_obra':
                    $resolvidas[$chave] = $d['cidade_obra'] ?? '';
                    break;
                case 'cidade_limpo':
                    $resolvidas[$chave] = $d['cidade_limpo'] ?? $d['cidade_obra'] ?? '';
                    break;
                case 'estado_obra':
                    $resolvidas[$chave] = $d['estado_obra'] ?? '';
                    break;
                case 'AreaEstimada':
                    $area = $d['area_obra'] ?? '0';
                    $unidade = $d['unidade_area'] ?? 'm²';
                    $resolvidas[$chave] = $area . ' ' . $unidade;
                    break;
                
                // ESCOPO
                case 'finalidade':
                    $resolvidas[$chave] = $d['finalidade'] ?? '';
                    break;
                case 'TipoTerreno':
                    $resolvidas[$chave] = $d['TipoTerreno'] ?? $d['tipo_terreno'] ?? 'Não informado';
                    break;
                case 'CoberturaVegetal':
                    $resolvidas[$chave] = $d['CoberturaVegetal'] ?? $d['cobertura_vegetal'] ?? 'Não informado';
                    break;
                case 'AcessoLocal':
                    $resolvidas[$chave] = $d['AcessoLocal'] ?? $d['acesso_local'] ?? 'Não informado';
                    break;
                case 'RestricoesAereas':
                    $resolvidas[$chave] = $d['RestricoesAereas'] ?? $d['restricoes_aereas'] ?? 'Não informado';
                    break;
                
                // VALORES - CORREÇÃO: Usar valor direto do extras
                case 'ValorProposta':
                    $valor = $d['valor_final_proposta'] ?? $d['ValorProposta'] ?? 0;
                    $resolvidas[$chave] = $this->formatarMoeda($valor);
                    break;
                case 'ValorExtenso':
                    $resolvidas[$chave] = $d['ValorExtenso'] ?? '';
                    break;
                case 'mobilizacao_percentual':
                    $resolvidas[$chave] = $d['mobilizacao_percentual'] ?? '30';
                    break;
                case 'mobilizacao_valor':
                    $valor = $d['mobilizacao_valor'] ?? 0;
                    $resolvidas[$chave] = $this->formatarMoeda($valor);
                    break;
                case 'restante_percentual':
                    $resolvidas[$chave] = $d['restante_percentual'] ?? '70';
                    break;
                case 'restante_valor':
                    $valor = $d['restante_valor'] ?? 0;
                    $resolvidas[$chave] = $this->formatarMoeda($valor);
                    break;
                
                // DATAS
                case 'DataExtenso':
                    $resolvidas[$chave] = $d['DataExtenso'] ?? $this->dataPorExtenso(time());
                    break;
                case 'numero_proposta':
                    $resolvidas[$chave] = $d['numero_proposta'] ?? '';
                    break;
                
                // BANCO
                case 'Banco':
                    $resolvidas[$chave] = $d['Banco'] ?? '';
                    break;
                case 'Agencia':
                    $resolvidas[$chave] = $d['Agencia'] ?? '';
                    break;
                case 'Conta':
                    $resolvidas[$chave] = $d['Conta'] ?? '';
                    break;
                case 'PIX':
                    $resolvidas[$chave] = $d['PIX'] ?? '';
                    break;
                
                // EQUIPAMENTOS
                case 'Drone':
                    $resolvidas[$chave] = $d['Drone'] ?? 'Não aplicável';
                    break;
                case 'GPS':
                    $resolvidas[$chave] = $d['GPS'] ?? 'Par de Receptores GNSS RTK';
                    break;
                case 'Estacao_Total':
                    $resolvidas[$chave] = $d['Estacao_Total'] ?? 'Não inclusa';
                    break;
                case 'Veiculo':
                    $resolvidas[$chave] = $d['Veiculo'] ?? 'Não incluso';
                    break;
                
                default:
                    // Fallback: tenta pegar direto do array
                    $resolvidas[$chave] = $d[$chave] ?? "[{$chave}]";
                    break;
            }
        }

        return $resolvidas;
    }

    private function buscarDadosEmpresa(int $id_usuario) 
    {
        $stmt = $this->conn->prepare("SELECT * FROM DadosEmpresa WHERE id_criador = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $id_usuario);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                // Checa logo
                if (!empty($row['logo_caminho'])) {
                    $row['logo_url'] = $row['logo_caminho'];
                } elseif (!empty($row['logo_url'])) {
                    // Mantem a logo_url externa
                } elseif (!empty($row['logo_empresa'])) {
                    $row['logo_url'] = 'uploads/' . $row['logo_empresa'];
                } else {
                    $row['logo_url'] = 'assets/logo_sgt.png';
                }
                return $row;
            }
        }
        return [
            'Empresa' => 'SGT Topografia',
            'CNPJ' => '',
            'WhatsApp' => '',
            'Telefone' => '',
            'Banco' => '',
            'Agencia' => '',
            'Conta' => '',
            'PIX' => '',
            'logo_url' => 'assets/logo_sgt.png'
        ];
    }
    
    /**
     * Tenta resolver o equipamento a partir da tabela filha de Locação (Master-Detail).
     * Caso não encontre, cai para o modelo antigo.
     */
    private function resolverEquipamento(array $d, string $tipoDesejado, string $chaveLegada, string $prefixoModelo, string $fallbackPadrao) {
        $itensLocacao = $d['itens']['locacao'] ?? $d['itensSalvos']['locacao'] ?? [];
        
        if (!empty($itensLocacao) && is_array($itensLocacao)) {
            $modelosEncontrados = [];
            foreach ($itensLocacao as $loc) {
                $tipoDado = strtolower($loc['tipo'] ?? '');
                // Ex: "drone_rtk" da match com "drone", "veiculo_4x4" dá match com "veiculo"
                if (strpos($tipoDado, strtolower($tipoDesejado)) !== false) {
                    if (!empty($loc['marca'])) {
                        $modelosEncontrados[] = $loc['marca'];
                    }
                }
            }
            if (!empty($modelosEncontrados)) {
                return implode(' e ', $modelosEncontrados);
            }
        }
        
        // Fallback legado
        return $d[$prefixoModelo] ?? $d[$chaveLegada] ?? $fallbackPadrao;
    }
    
    private function formatarMoeda($valor) {
        if (empty($valor)) return '0,00';
        
        $valorStr = str_replace(['R$', 'r$', ' '], '', (string)$valor);
        
        if (is_numeric($valorStr)) {
            $num = (float)$valorStr;
        } else {
            $valorStr = preg_replace('/[^0-9.,\-]/', '', $valorStr);
            if (strpos($valorStr, ',') !== false) {
                $valorStr = str_replace('.', '', $valorStr);
                $valorStr = str_replace(',', '.', $valorStr);
            }
            $num = (float)$valorStr;
        }
        
        return number_format($num, 2, ',', '.');
    }
    
    private function valorPorExtenso($valor) {
        $valorStr = str_replace(['R$', 'r$', ' '], '', (string)$valor);
        
        if (is_numeric($valorStr)) {
            $num = (float)$valorStr;
        } else {
            $valorStr = preg_replace('/[^0-9.,\-]/', '', $valorStr);
            if (strpos($valorStr, ',') !== false) {
                $valorStr = str_replace('.', '', $valorStr);
                $valorStr = str_replace(',', '.', $valorStr);
            }
            $num = (float)$valorStr;
        }

        if ($num == 0) return 'ZERO REAIS';
        
        if (class_exists('NumberFormatter')) {
            $fmt = new NumberFormatter("pt_BR", NumberFormatter::SPELLOUT);
            return mb_strtoupper($fmt->format($num) . " REAIS");
        }
        return 'R$ ' . number_format($num, 2, ',', '.') . ' (VALOR)';
    }
    
    private function dataPorExtenso($data) {
        $meses = [1=>'janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
        $ts = is_string($data) ? strtotime($data) : ($data ?? time());
        return date('d', $ts) . ' de ' . $meses[intval(date('n', $ts))] . ' de ' . date('Y', $ts);
    }
}
