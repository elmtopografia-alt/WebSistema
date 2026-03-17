# CONTRATO DE MAPEAMENTO DOCX v3.0 - SGT PROPOSTAS
Este documento define o padrão oficial de variáveis para os novos templates DOCX.

## 1. DADOS DO CLIENTE (SNAPSHOT)
| Chave no DOCX | Origem no Banco (Coluna) | Descrição |
| :--- | :--- | :--- |
| `${nome_cliente}` | `nome_cliente_salvo` | Nome da Pessoa Física ou Razão Social |
| `${nome_contato}` | `contato_obra` | Nome da pessoa de contato/responsável |
| `${email_cliente}` | `email_salvo` | E-mail oficial do cliente para a proposta |
| `${whatsapp_cliente}` | `whatsapp_salvo` | WhatsApp de contato |
| `${telefone_cliente}` | `telefone_salvo` | Telefone fixo (opcional) |

## 2. DADOS DA EMPRESA PROPONENTE
| Chave no DOCX | Origem no Banco (Tabela DadosEmpresa) | Descrição |
| :--- | :--- | :--- |
| `${empresa}` | `Empresa` | Nome da sua empresa |
| `${cnpj}` | `CNPJ` | CNPJ da sua empresa |
| `${logo}` | `logo_url` | Placeholder para inserção automática da logomarca |
| `${cidade_empresa}` | `Cidade` | Cidade onde a empresa está sediada |

## 3. DETALHES DA PROPOSTA E OBRA
| Chave no DOCX | Origem no Banco / Cálculo | Descrição |
| :--- | :--- | :--- |
| `${numero_proposta}` | `numero_proposta` | Identificador único (Ex: ABC-2024-001) |
| `${data_extenso}` | (Calculado Dinamicamente) | Data formatada (Ex: 17 de março de 2026) |
| `${endereco_obra}` | `endereco_obra` | Logradouro e número da obra |
| `${bairro_obra}` | `bairro_obra` | Bairro da obra |
| `${cidade_obra}` | `cidade_obra` | Cidade onde o serviço será prestado |
| `${finalidade}` | `finalidade` | Objetivo do levantamento (Seção 2) |
| `${area_obra}` | `area_obra` + `unidade_area` | Área total do levantamento |
| `${escopo}` | `escopo_servico` | Texto longo com os detalhes dos serviços (Seção 3) |
| `${metodologia}` | `metodologia` | Descrição técnica da execução (Seção 4) |

## 4. VALORES FINANCEIROS (FORMATADOS)
| Chave no DOCX | Origem no Banco (Coluna) | Descrição |
| :--- | :--- | :--- |
| `${valor_total}` | `valor_final_proposta` | Valor total com descontos (R$ 0.000,00) |
| `${valor_extenso}` | `Valor_proposta_extenso` | Valor total escrito por extenso |
| `${valor_entrada}` | `mobilizacao_valor` | Valor do sinal/entrada (Geralmente 30%) |
| `${valor_restante}` | `restante_valor` | Valor do saldo final (Geralmente 70%) |

## 5. DADOS BANCÁRIOS (DINÂMICOS)
| Chave no DOCX | Origem no Banco (DadosEmpresa) | Descrição |
| :--- | :--- | :--- |
| `${banco}` | `Banco` | Nome da instituição financeira |
| `${agencia}` | `Agencia` | Número da agência |
| `${conta}` | `Conta` | Número da conta corrente |
| `${chave_pix}` | `PIX` | Chave PIX oficial para pagamento |

## ⚠️ REGRAS DE OURO PARA NOVOS TEMPLATES
1. **Minúsculas**: Use sempre letras minúsculas nas tags `${...}` para evitar erros de digitação.
2. **Sem Acentos**: Evite acentos dentro das tags (Ex: use `${endereco_obra}` e não `${endereço_obra}`).
3. **Snake Case**: Use sublinhado `_` para separar as palavras.
4. **Tags Banidas**: NÃO use mais `mail`, `e-mail`, `valorextenso`, `Cidade`. Substitua pelos nomes acima.
