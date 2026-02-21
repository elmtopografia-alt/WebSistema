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
            // Mapeamento Direto
            switch ($chave) {
                // Empresa / Usuario
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
                case 'Banco':
                    $resolvidas[$chave] = $empresa['Banco'] ?? '';
                    break;
                case 'Agencia':
                    $resolvidas[$chave] = $empresa['Agencia'] ?? '';
                    break;
                case 'Conta':
                    $resolvidas[$chave] = $empresa['Conta'] ?? '';
                    break;
                case 'PIX':
                case 'pix':
                    $resolvidas[$chave] = $empresa['PIX'] ?? '';
                    break;
                case 'logo_empresa':
                case 'logo':
                    $resolvidas[$chave] = $empresa['logo_url'] ?? '';
                    break;
                
                // Cliente
                case 'nome_cliente_salvo':
                case 'nome_cliente':
                    $resolvidas[$chave] = $d['nome_cliente'] ?? $d['nome_cliente_salvo'] ?? 'Cliente não informado';
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
                case 'whatsapp_salvo':
                case 'celular_cliente':
                    $resolvidas[$chave] = $d['whatsapp_salvo'] ?? $d['celular_salvo'] ?? $d['celular_cliente'] ?? '';
                    break;
                
                // Obra / Terreno
                case 'endereco_obra':
                    $resolvidas[$chave] = $d['endereco_obra'] ?? '';
                    break;
                case 'bairro_obra':
                case 'ClienteBairro':
                    $resolvidas[$chave] = $d['bairro_obra'] ?? '';
                    break;
                case 'cidade_obra':
                    $resolvidas[$chave] = $d['cidade_obra'] ?? '';
                    break;
                case 'estado_obra':
                case 'uf_obra':
                    $resolvidas[$chave] = $d['estado_obra'] ?? '';
                    break;
                case 'ClienteCidadeUF':
                    $resolvidas[$chave] = trim(($d['cidade_obra'] ?? '') . '-' . ($d['estado_obra'] ?? ''), '-');
                    break;
                case 'AreaEstimada':
                    $resolvidas[$chave] = ($d['area_obra'] ?? '0') . ' ' . ($d['unidade_area'] ?? 'm²');
                    break;
                case 'unidade_area':
                    $resolvidas[$chave] = $d['unidade_area'] ?? 'm²';
                    break;
                case 'TipoTerreno':
                    $resolvidas[$chave] = $d['tipo_terreno'] ?? 'Não informado';
                    break;
                case 'CoberturaVegetal':
                    $resolvidas[$chave] = $d['cobertura_vegetal'] ?? 'Não informado';
                    break;
                case 'AcessoLocal':
                    $resolvidas[$chave] = $d['acesso_local'] ?? 'Não informado';
                    break;
                case 'RestricoesAereas':
                    $resolvidas[$chave] = $d['restricoes_aereas'] ?? 'Não informado';
                    break;
                
                // Equipamentos
                case 'Drone':
                    $resolvidas[$chave] = $d['drone'] ?? 'Não aplicável';
                    break;
                case 'Veiculo':
                    $resolvidas[$chave] = $d['veiculo'] ?? 'Não incluso';
                    break;
                case 'Estacao_Total':
                    $resolvidas[$chave] = $d['estacao_total'] ?? 'Não inclusa';
                    break;
                case 'GPS':
                    $resolvidas[$chave] = $d['gps'] ?? 'Par de Receptores GNSS RTK';
                    break;
                
                // Proposta / Valores
                case 'numero_proposta':
                    $resolvidas[$chave] = $d['numero_proposta'] ?? '';
                    break;
                case 'status':
                    $resolvidas[$chave] = $d['status'] ?? 'Em elaboração';
                    break;
                case 'finalidade':
                    $resolvidas[$chave] = $d['finalidade'] ?? '';
                    break;
                case 'tipo_levantamento':
                    $resolvidas[$chave] = $d['tipo_levantamento'] ?? '';
                    break;
                case 'ValorProposta':
                    $resolvidas[$chave] = $this->formatarMoeda($d['valor_final_proposta'] ?? 0);
                    break;
                case 'ValorExtenso':
                    $resolvidas[$chave] = $this->valorPorExtenso($d['valor_final_proposta'] ?? 0);
                    break;
                case 'prazo_execucao':
                    $resolvidas[$chave] = $d['prazo_execucao'] ?? '';
                    break;
                case 'dias_campo':
                    $resolvidas[$chave] = $d['dias_campo'] ?? '0';
                    break;
                case 'dias_escritorio':
                    $resolvidas[$chave] = $d['dias_escritorio'] ?? '0';
                    break;
                case 'mobilizacao_percentual':
                    $resolvidas[$chave] = $d['mobilizacao_percentual'] ?? '30';
                    break;
                case 'mobilizacao_valor':
                    $resolvidas[$chave] = $this->formatarMoeda($d['mobilizacao_valor'] ?? 0);
                    break;
                case 'restante_percentual':
                    $resolvidas[$chave] = $d['restante_percentual'] ?? '70';
                    break;
                case 'restante_valor':
                    $resolvidas[$chave] = $this->formatarMoeda($d['restante_valor'] ?? 0);
                    break;
                
                // Datas
                case 'DataExtenso':
                    $resolvidas[$chave] = $this->dataPorExtenso($d['data_criacao'] ?? time());
                    break;
                case 'DataHoje':
                    $ts = is_string($d['data_criacao'] ?? null) ? strtotime($d['data_criacao']) : time();
                    $resolvidas[$chave] = date('d/m/Y', $ts);
                    break;
                
                default:
                    // Se não tiver regra específica, tenta puxar do array $d direto
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
                if (empty($row['logo_url'])) {
                    if (!empty($row['logo_empresa'])) {
                        $row['logo_url'] = 'uploads/' . $row['logo_empresa'];
                    } else {
                        $row['logo_url'] = 'assets/logo_sgt.png';
                    }
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
