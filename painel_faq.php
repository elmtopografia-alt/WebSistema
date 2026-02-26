<?php
/**
 * Painel de Dúvidas Recorrentes - SGT Propostas
 * Abre em nova janela para suporte ao editor_dinamico.php
 */

session_start();
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central de Ajuda - SGT Propostas</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --brand: #b45f06;
            --brand-light: #d4a574;
            --brand-bg: #fdf8f3;
            --text-primary: #1a1a2e;
            --text-secondary: #4a4a68;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --surface: #f8fafc;
            --success: #059669;
            --warning: #d97706;
            --error: #dc2626;
            --info: #2563eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: white;
            border-bottom: 3px solid var(--brand);
            padding: 1.5rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: var(--brand);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(180, 95, 6, 0.3);
        }

        .logo-text h1 {
            font-size: 1.25rem;
            color: var(--text-primary);
            margin: 0;
        }

        .logo-text p {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            border: none;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--brand);
            color: white;
        }

        .btn-primary:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(180, 95, 6, 0.3);
        }

        .btn-secondary {
            background: white;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--surface);
            border-color: var(--brand-light);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Search Section */
        .search-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .search-section h2 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .search-section p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .search-box {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-box input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s;
            font-family: inherit;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(180, 95, 6, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* Quick Tags */
        .quick-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .tag {
            padding: 0.375rem 0.875rem;
            background: var(--brand-bg);
            color: var(--brand);
            border-radius: 20px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .tag:hover {
            background: var(--brand);
            color: white;
            transform: translateY(-1px);
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .category-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid var(--brand);
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.15);
        }

        .category-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--brand-bg) 0%, white 100%);
            border-bottom: 1px solid var(--border);
        }

        .category-icon {
            width: 48px;
            height: 48px;
            background: var(--brand);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .category-header h3 {
            color: var(--text-primary);
            font-size: 1.125rem;
            margin-bottom: 0.25rem;
        }

        .category-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .faq-list {
            padding: 1rem;
        }

        .faq-item {
            border-bottom: 1px solid var(--border);
            overflow: hidden;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            padding: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.9375rem;
        }

        .faq-question:hover {
            background: var(--surface);
        }

        .faq-question i {
            transition: transform 0.3s;
            color: var(--brand);
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background: var(--surface);
        }

        .faq-answer-content {
            padding: 0 1rem 1rem;
            color: var(--text-secondary);
            font-size: 0.9375rem;
            line-height: 1.7;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
        }

        /* Highlight */
        .highlight {
            background: linear-gradient(120deg, rgba(180, 95, 6, 0.2) 0%, rgba(180, 95, 6, 0.2) 100%);
            padding: 0 2px;
            border-radius: 2px;
        }

        /* Code blocks */
        code {
            background: #f1f5f9;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.875em;
            color: var(--brand);
        }

        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 0.5rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }

        /* Status indicators */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-success {
            background: #d1fae5;
            color: var(--success);
        }

        .status-warning {
            background: #fef3c7;
            color: var(--warning);
        }

        /* Alert Box */
        .alert {
            background: var(--brand-bg);
            border-left: 4px solid var(--brand);
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .alert-icon {
            color: var(--brand);
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }

        .alert-content h4 {
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .alert-content p {
            color: var(--text-secondary);
            font-size: 0.9375rem;
            margin: 0;
        }

        /* No results */
        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
            display: none;
        }

        .no-results.show {
            display: block;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .categories-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        /* Print styles */
        @media print {
            .header, .search-section {
                display: none;
            }
            
            .faq-answer {
                max-height: none !important;
            }
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="header-content">
            <div class="logo-area">
                <div class="logo-icon">
                    <i class="fas fa-life-ring"></i>
                </div>
                <div class="logo-text">
                    <h1>Central de Ajuda</h1>
                    <p>SGT Propostas - Editor Dinâmico</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button class="btn btn-primary" onclick="window.close()">
                    <i class="fas fa-times"></i> Fechar
                </button>
            </div>
        </div>
    </header>

    <div class="container">
        
        <!-- Search -->
        <div class="search-section">
            <h2>Como podemos ajudar?</h2>
            <p>Busque respostas sobre o editor dinâmico, modelos DOCX e geração de propostas</p>
            
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Digite sua dúvida (ex: 'campos dinâmicos', 'DOCX', 'salvar')...">
            </div>
            
            <div class="quick-tags">
                <span class="tag" onclick="filterByTag('docx')">DOCX</span>
                <span class="tag" onclick="filterByTag('variáveis')">Variáveis</span>
                <span class="tag" onclick="filterByTag('salvar')">Salvar Proposta</span>
                <span class="tag" onclick="filterByTag('visualizar')">Visualizar</span>
                <span class="tag" onclick="filterByTag('erros')">Erros Comuns</span>
                <span class="tag" onclick="filterByTag('banco')">Banco de Dados</span>
            </div>
        </div>

        <!-- Categories -->
        <div class="categories-grid" id="categoriesGrid">
            
            <!-- Categoria 1: Primeiros Passos -->
            <div class="category-card" data-category="inicio">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Primeiros Passos</h3>
                    <p>Entenda o fluxo do editor dinâmico</p>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>O que é o "Fluxo DOCX V3"?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                O <strong>Fluxo DOCX V3</strong> é a nova arquitetura do editor que permite carregar modelos de proposta diretamente de arquivos DOCX. Diferente do sistema antigo (hardcoded), ele lê dinamicamente a estrutura do documento e mapeia automaticamente os campos <code>{{variavel}}</code> encontrados no modelo.
                                <br><br>
                                <span class="status status-success"><i class="fas fa-check"></i> Ativo</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Por que 99% dos dados já vêm preenchidos?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Isso ocorre porque o sistema utiliza <strong>dados do cliente e da obra</strong> já cadastrados no banco. Quando você abre o editor, ele automaticamente preenche campos como nome, email, telefone e dados da obra. Os 1% restantes são campos específicos do modelo DOCX que podem precisar de ajuste manual.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Qual a diferença entre "Modelo" e "Dados"?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <ul style="margin-left: 1.2rem; margin-top: 0.5rem;">
                                    <li><strong>Modelo (DOCX):</strong> É o "esqueleto" da proposta - contém o texto fixo, formatação e as variáveis <code>{{exemplo}}</code></li>
                                    <li><strong>Dados:</strong> São os valores que substituem as variáveis (nome do cliente, valor do serviço, etc.)</li>
                                </ul>
                                <div class="alert" style="margin-top: 1rem;">
                                    <i class="fas fa-lightbulb alert-icon"></i>
                                    <div class="alert-content">
                                        <h4>Dica Pro</h4>
                                        <p>O contexto do modelo está 100% carregado no editor, mas você pode editar qualquer campo antes de salvar.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categoria 2: Variáveis e Campos -->
            <div class="category-card" data-category="variaveis">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3>Variáveis e Campos Dinâmicos</h3>
                    <p>Trabalhando com {{placeholders}}</p>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Como funcionam as chaves {{variavel}}?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                As chaves (placeholders) são marcadores no modelo DOCX que serão substituídos por dados reais. Sintaxe suportada:
                                <div class="code-block">
{{nome_cliente}}<br>
{{valor_total}}<br>
{{data_atual|d/m/Y}}<br>
{{condicao_especial|default:"Nenhuma"}}
                                </div>
                                O sistema faz o parse automático e cria campos editáveis no editor.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Adicionei um campo no DOCX mas não aparece no editor</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Verifique:
                                <ol style="margin-left: 1.2rem; margin-top: 0.5rem;">
                                    <li>Se o campo segue o padrão <code>{{nome_campo}}</code> (sem espaços, apenas underline)</li>
                                    <li>Se o arquivo DOCX foi reenviado após a alteração</li>
                                    <li>Se não há formatação quebrada dentro das chaves (negrito parcial, etc.)</li>
                                </ol>
                                <br>
                                <span class="status status-warning"><i class="fas fa-exclamation-triangle"></i> Cache</span> Limpe o cache do navegador (Ctrl+F5) após atualizar modelos.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Campos de email/telefone não estão salvando</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Este é um problema conhecido de mapeamento legado. O <code>salvar_proposta.php</code> precisa detectar se está no "modo DOCX" e extrair campos dinâmicos.
                                <br><br>
                                <strong>Solução temporária:</strong> Verifique se o nome do campo no DOCX corresponde exatamente à coluna no banco de dados (ex: <code>{{email}}</code> vs coluna <code>email</code>).
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categoria 3: Salvamento -->
            <div class="category-card" data-category="salvar">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="fas fa-save"></i>
                    </div>
                    <h3>Salvamento e Persistência</h3>
                    <p>Problemas com salvar_proposta.php</p>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Erro: "Campos dinâmicos não detectados"</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                O <code>salvar_proposta.php</code> precisa ser atualizado para identificar campos com prefixo <code>docx_bloco_X_content</code>.
                                <br><br>
                                <strong>Checklist de debug:</strong>
                                <ul style="margin-left: 1.2rem; margin-top: 0.5rem;">
                                    <li>Verifique no Network (F12) se os dados estão sendo POSTados</li>
                                    <li>Confirme se o campo hidden <code>modo_docx</code> está presente no formulário</li>
                                    <li>Valide se o JSON dos blocos está sendo enviado corretamente</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Como funciona o PropostaRepository no modo DOCX?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                O padrão Repository deve detectar o modo de operação:
                                <div class="code-block">
if ($data['modo_docx']) {
    // Extrair campos dinâmicos de docx_bloco_*
    $camposDinamicos = $this->extrairCamposDOCX($data);
    return $this->salvarModoDOCX($camposDinamicos);
} else {
    // Modo legado (hardcoded)
    return $this->salvarModoLegado($data);
}
                                </div>
                                Isso mantém <strong>backward compatibility</strong> com propostas antigas.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categoria 4: Visualização -->
            <div class="category-card" data-category="visualizar">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Visualização e PDF</h3>
                    <p>Botão Visualizar Web e geração</p>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Botão "Visualizar Web" não funciona</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                O botão Visualizar Web precisa:
                                <ol style="margin-left: 1.2rem; margin-top: 0.5rem;">
                                    <li>Salvar os dados temporariamente (draft)</li>
                                    <li>Abrir <code>gerar-proposta.php?id=X&preview=1</code> em nova aba</li>
                                    <li>Passar o parâmetro <code>modo=docx</code> para usar o parser correto</li>
                                </ol>
                                <br>
                                <strong>Implementação sugerida:</strong>
                                <div class="code-block">
$('#btnVisualizar').click(function() {
    // 1. Salvar rascunho via AJAX
    // 2. Abrir preview em nova aba
    window.open('gerar-proposta.php?id=' + id + '&preview=1', '_blank');
});
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>HTML gerado está desconfigurado</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Se o HTML sair "quebrado" (estilos inline perdidos, tags mal fechadas):
                                <ul style="margin-left: 1.2rem; margin-top: 0.5rem;">
                                    <li>Verifique o parser DOCX→HTML (provavelmente usando PHPWord ou similar)</li>
                                    <li>Confirme se o CSS do tema está sendo injetado no cabeçalho</li>
                                    <li>Valide se não há conflito entre estilos do DOCX e estilos do tema</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categoria 5: Erros Comuns -->
            <div class="category-card" data-category="erros">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="fas fa-bug"></i>
                    </div>
                    <h3>Erros Comuns e Debug</h3>
                    <p>Soluções rápidas para problemas frequentes</p>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Editor trava ao carregar modelo grande</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Modelos DOCX muito grandes (>5MB) ou com muitas imagens podem causar timeout.
                                <br><br>
                                <strong>Soluções:</strong>
                                <ul style="margin-left: 1.2rem; margin-top: 0.5rem;">
                                    <li>Aumente <code>max_execution_time</code> no PHP</li>
                                    <li>Otimize imagens no DOCX antes de enviar</li>
                                    <li>Implemente carregamento assíncrono (lazy load) para blocos</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Caracteres especiais (ç, ã, º) aparecem errado</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Problema de encoding. Verifique:
                                <ol style="margin-left: 1.2rem; margin-top: 0.5rem;">
                                    <li>Banco de dados está em UTF-8 (charset=utf8mb4)</li>
                                    <li>Arquivo PHP tem <code>header('Content-Type: text/html; charset=utf-8')</code></li>
                                    <li>DOCX foi salvo corretamente (evite copiar do Word direto com formatação estranha)</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categoria 6: Banco de Dados -->
            <div class="category-card" data-category="banco">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>Banco de Dados</h3>
                    <p>Migrações e Schema</p>
                </div>
                <div class="faq-list">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Quais colunas adicionar para suporte DOCX?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                SQL de migração sugerido:
                                <div class="code-block">
ALTER TABLE propostas 
ADD COLUMN modo_geracao VARCHAR(20) DEFAULT 'legado' AFTER status,
ADD COLUMN estrutura_docx JSON NULL AFTER modo_geracao,
ADD COLUMN campos_dinamicos JSON NULL AFTER estrutura_docx;
                                </div>
                                Isso permite armazenar a estrutura completa do DOCX sem quebrar dados existentes.
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Como manter compatibilidade com propostas antigas?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                Use o campo <code>modo_geracao</code> como flag:
                                <ul style="margin-left: 1.2rem; margin-top: 0.5rem;">
                                    <li><code>'legado'</code> → Usa campos hardcoded (cabecalho, dados_cliente, etc.)</li>
                                    <li><code>'docx'</code> → Usa estrutura dinâmica do JSON</li>
                                </ul>
                                <br>
                                No <code>gerar-proposta.php</code>, verifique este campo antes de decidir qual renderer usar.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="no-results" id="noResults">
            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <h3>Nenhum resultado encontrado</h3>
            <p>Tente buscar com termos diferentes ou entre em contato com o suporte técnico.</p>
        </div>

        <div class="footer">
            <p>SGT Propostas SaaS • Sistema de Gestão Técnica</p>
            <p style="margin-top: 0.5rem; font-size: 0.75rem;">Documentação gerada automaticamente • Última atualização: Fevereiro 2026</p>
        </div>

    </div>

    <script>
        // Toggle FAQ
        function toggleFaq(element) {
            const item = element.parentElement;
            const isActive = item.classList.contains('active');
            
            // Close all in same category
            const category = item.closest('.faq-list');
            category.querySelectorAll('.faq-item').forEach(faq => {
                faq.classList.remove('active');
            });
            
            // Toggle current
            if (!isActive) {
                item.classList.add('active');
            }
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const noResults = document.getElementById('noResults');
        const categoriesGrid = document.getElementById('categoriesGrid');

        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.faq-item');
            const cards = document.querySelectorAll('.category-card');
            let hasResults = false;

            items.forEach(item => {
                const question = item.querySelector('.faq-question span').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer-content').textContent.toLowerCase();
                
                if (question.includes(term) || answer.includes(term)) {
                    item.style.display = 'block';
                    if (term.length > 0) {
                        item.classList.add('active');
                        // Highlight text
                        highlightText(item, term);
                    } else {
                        item.classList.remove('active');
                        removeHighlight(item);
                    }
                    hasResults = true;
                } else {
                    item.style.display = term.length === 0 ? 'block' : 'none';
                    if (term.length === 0) {
                        item.classList.remove('active');
                    }
                }
            });

            // Show/hide cards based on visible items
            cards.forEach(card => {
                const visibleItems = card.querySelectorAll('.faq-item[style*="block"], .faq-item:not([style*="none"])');
                if (visibleItems.length === 0 && term.length > 0) {
                    card.style.display = 'none';
                } else {
                    card.style.display = 'block';
                }
            });

            noResults.classList.toggle('show', !hasResults && term.length > 0);
        });

        function highlightText(element, term) {
            // Simple highlight implementation
            const content = element.querySelector('.faq-answer-content');
            if (!content || term.length < 2) return;
            
            // Store original if not stored
            if (!content.dataset.original) {
                content.dataset.original = content.innerHTML;
            }
            
            const regex = new RegExp(`(${term})`, 'gi');
            content.innerHTML = content.dataset.original.replace(regex, '<span class="highlight">$1</span>');
        }

        function removeHighlight(element) {
            const content = element.querySelector('.faq-answer-content');
            if (content && content.dataset.original) {
                content.innerHTML = content.dataset.original;
            }
        }

        // Filter by tag
        function filterByTag(tag) {
            searchInput.value = tag;
            searchInput.dispatchEvent(new Event('input'));
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
            }
            if (e.key === 'Escape') {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
            }
        });

        // Open all FAQs when printing
        window.addEventListener('beforeprint', function() {
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.add('active');
            });
        });
    </script>

</body>
</html>
