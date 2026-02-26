#!/usr/bin/env python3
"""
Conversor DOCX -> Estrutura JSON para SGT Propostas V2.0
Preserva formatação, tabelas, imagens, detecta variáveis e GERA CSS DINÂMICO por seção
"""

import sys
import json
import re
import mammoth
from docx import Document
from docx.shared import Pt, Inches, RGBColor
import base64
import os
import importlib.util

# =====================================================
# CONFIGURAÇÃO DE CORES POR SEÇÃO (Ciclo Azul→Marrom→Cinza)
# =====================================================
CORES_SECAO = [
    {'cor': '#1e40af', 'borda': '#3b82f6', 'nome': 'Azul'},      # Seção 1
    {'cor': '#7c2d12', 'borda': '#92400e', 'nome': 'Marrom'},    # Seção 2
    {'cor': '#374151', 'borda': '#6b7280', 'nome': 'Cinza'},     # Seção 3
    {'cor': '#1e3a8a', 'borda': '#1d4ed8', 'nome': 'Azul Escuro'}, # Seção 4
    {'cor': '#92400e', 'borda': '#b45309', 'nome': 'Marrom Escuro'}, # Seção 5
    {'cor': '#4b5563', 'borda': '#9ca3af', 'nome': 'Cinza Claro'},  # Seção 6
    {'cor': '#2563eb', 'borda': '#60a5fa', 'nome': 'Azul Claro'},   # Seção 7
    {'cor': '#78350f', 'borda': '#a16207', 'nome': 'Marrom Claro'}, # Seção 8
    {'cor': '#1f2937', 'borda': '#4b5563', 'nome': 'Cinza Escuro'}, # Seção 9
    {'cor': '#1d4ed8', 'borda': '#3b82f6', 'nome': 'Azul Médio'},   # Seção 10
]

# Variáveis do sistema que devem ser sempre incluídas
VARIAVEIS_SISTEMA = [
    'cidade_limpo',      # Cidade sem estado (para cabeçalho)
    'DataExtenso',       # Data por extenso
    'numero_proposta',   # Número da proposta
    'whatsapp',          # WhatsApp da empresa
]

def extrair_estilos(paragraph):
    """Extrai estilos CSS de um parágrafo do Word"""
    estilos = {}
    
    if paragraph.style and paragraph.style.font:
        font = paragraph.style.font
        if font.size:
            estilos['font-size'] = f"{font.size.pt}px"
        if font.bold:
            estilos['font-weight'] = 'bold'
        if font.italic:
            estilos['font-style'] = 'italic'
        if font.color and font.color.rgb:
            estilos['color'] = f"#{font.color.rgb}"
    
    # Alinhamento
    alignment_map = {
        0: 'left', 1: 'center', 2: 'right', 3: 'justify'
    }
    if paragraph.alignment is not None:
        estilos['text-align'] = alignment_map.get(paragraph.alignment, 'left')
    
    return estilos

def processar_tabela(table):
    """Converte tabela DOCX em estrutura HTML/CSS"""
    rows = []
    for row in table.rows:
        cells = []
        for cell in row.cells:
            # Extrai texto e estilos da célula
            textos = []
            for para in cell.paragraphs:
                if para.text.strip():
                    textos.append(para.text)
            
            cells.append({
                'texto': ' '.join(textos),
                'colspan': cell._tc.grid_span if hasattr(cell._tc, 'grid_span') else 1,
                'estilos': {
                    'border': '1px solid #dee2e6',
                    'padding': '12px 15px',
                    'background': '#f8f9fa' if len(rows) == 0 else 'transparent',
                    'vertical-align': 'top'
                }
            })
        rows.append(cells)
    
    return {
        'tipo': 'tabela',
        'linhas': rows,
        'estilos': {
            'width': '100%',
            'border-collapse': 'collapse',
            'margin': '25px 0',
            'font-size': '14px'
        }
    }

def detectar_variaveis(texto):
    """Detecta ${var}, ${ var }, {{var}} ou {{ var }} no texto"""
    padrao = r'\$\{\s*(\w+)\s*\}|\{\{\s*(\w+)\s*\}\}'
    matches = re.findall(padrao, texto)
    return list(set([m[0] or m[1] for m in matches if m[0] or m[1]]))

def detectar_variaveis_implicitas(texto):
    """Detecta variáveis que devem ser adicionadas implicitamente baseado no contexto"""
    vars_implicitas = []
    texto_lower = texto.lower()
    
    # Se menciona cidade + data, provavelmente precisa de cidade_limpo
    if any(x in texto_lower for x in ['cidade', 'data', 'extenso']) and '${cidade' in texto:
        vars_implicitas.append('cidade_limpo')
    
    # Se tem número de proposta
    if 'proposta' in texto_lower and ('nº' in texto_lower or 'numero' in texto_lower):
        vars_implicitas.append('numero_proposta')
    
    return vars_implicitas

def processar_imagens(doc, docx_path):
    """Extrai imagens embutidas e converte para base64"""
    imagens = []
    rels = doc.part.rels
    
    for rel in rels.values():
        if "image" in rel.target_ref:
            try:
                image = rel.target_part.blob
                ext = rel.target_ref.split('.')[-1]
                base64_img = base64.b64encode(image).decode('utf-8')
                imagens.append({
                    'id': rel.rId,
                    'extensao': ext,
                    'base64': f"data:image/{ext};base64,{base64_img}",
                    'tamanho': len(image)
                })
            except Exception as e:
                pass
    
    return imagens

def classificar_bloco(texto, estilos, nivel_titulo):
    """Classifica o bloco por conteúdo e contexto"""
    texto_lower = texto.strip().lower()
    
    # Se já detectou como título pelo nível, prioriza
    if nivel_titulo > 0:
        return 'titulo'
    
    # Detecção por conteúdo específico
    if any(x in texto_lower for x in ['cliente', 'contratante', 'nome:', 'e-mail:', 'email:']):
        return 'dados_cliente'
    elif any(x in texto_lower for x in ['obra', 'local', 'terreno', 'endereço:', 'bairro:', 'cidade/estado:']):
        return 'dados_obra'
    elif any(x in texto_lower for x in ['valor', 'investimento', 'r$', 'preço', 'mobilização', 'restante']):
        return 'valores'
    elif any(x in texto_lower for x in ['prazos', 'dias úteis', 'cronograma', 'etapa', 'mobilização']):
        return 'prazos'
    elif any(x in texto_lower for x in ['pagamento', 'banco:', 'pix:', 'agência:', 'conta:']):
        return 'pagamento'
    elif any(x in texto_lower for x in ['equipamento', 'vante', 'drone', 'gps', 'rtk', 'estação total', 'veículo']):
        return 'equipamentos'
    elif estilos.get('font-weight') == 'bold' and len(texto) < 120:
        return 'titulo'
    else:
        return 'texto_geral'

def detectar_nivel_titulo(para):
    """Detecta se é H1, H2, H3 baseado no estilo do Word ou numeração manual"""
    texto = para.text.strip()
    if not texto: 
        return 0
    
    style_name = para.style.name if para.style else ''
    
    # 1. Checa Estilos Oficiais do Word
    if 'Heading 1' in style_name or 'Título 1' in style_name: 
        return 1
    if 'Heading 2' in style_name or 'Título 2' in style_name: 
        return 2
    if 'Heading 3' in style_name or 'Título 3' in style_name: 
        return 3
    
    # 2. Heurística: Numeração Manual (Ex: "5. Equipamentos")
    # Títulos principais (X.) -> H2 (No SGT h1 é reservado para o título da proposta)
    if re.match(r'^\d+\.\s+[A-Z]', texto):
        return 2
    # Subtítulos (X.X ou X.X.X) -> H3
    if re.match(r'^\d+\.\d+(\.\d+)?\s+', texto):
        return 3
    # FASE X -> H3
    if re.match(r'^FASE\s+\d+', texto, re.IGNORECASE):
        return 3
        
    # 3. Fallback para Texto em Caixa Alta (Título principal se for curto)
    if texto.isupper() and len(texto) < 100:
        return 1
        
    return 0

def gerar_css_dinamico(blocos):
    """Gera CSS dinâmico com cores alternadas por seção de título H2"""
    css_base = """
        .modelo-docx { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 900px; margin: 0 auto; }
        
        /* Título principal H1 */
        .modelo-docx h1 { 
            color: #1e3a8a; 
            border-bottom: 3px solid #1e3a8a; 
            padding-bottom: 10px; 
            font-size: 24px; 
            font-weight: bold; 
            text-align: center;
        }
        
        /* Subtítulos H3 */
        .modelo-docx h3 { 
            color: #4b5563; 
            font-size: 14px; 
            font-weight: bold; 
            margin-top: 20px; 
        }
        
        /* Classes utilitárias */
        .titulo-principal { color: #1e3a8a; border-bottom: 3px solid #1e3a8a; padding-bottom: 10px; }
        .titulo-secao { color: #1e40af; margin-top: 25px; }
        .var-placeholder { background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-family: monospace; border: 1px dashed #3b82f6; }
        .tabela-proposta th { background: #f1f5f9; font-weight: 600; }
        .dados_cliente, .dados_obra { background: #f8fafc; padding: 15px; border-radius: 8px; margin: 10px 0; }
    """
    
    # Conta apenas os H2 para aplicar cores alternadas
    contador_h2 = 0
    css_secoes = []
    
    for bloco in blocos:
        if bloco.get('nivel_titulo') == 2:
            cor = CORES_SECAO[contador_h2 % len(CORES_SECAO)]
            css_secoes.append(f"""
        /* Seção {contador_h2 + 1} - {cor['nome']} */
        .modelo-docx h2:nth-of-type({contador_h2 + 1}) {{ 
            color: {cor['cor']}; 
            margin-top: 25px; 
            font-size: 18px; 
            font-weight: bold; 
            border-left: 4px solid {cor['borda']}; 
            padding-left: 12px; 
        }}""")
            contador_h2 += 1
    
    return css_base + '\\n'.join(css_secoes)

def converter_docx(caminho_docx):
    """Função principal de conversão V2.0"""
    
    if not os.path.exists(caminho_docx):
        return {'sucesso': False, 'erro': f'Arquivo nao encontrado: {caminho_docx}'}

    doc = Document(caminho_docx)
    
    # Extrai imagens primeiro
    imagens = processar_imagens(doc, caminho_docx)
    
    blocos = []
    variaveis_globais = set(VARIAVEIS_SISTEMA)  # Inicia com variáveis do sistema
    
    # Primeira passada: detectar estrutura de títulos
    for element in doc.element.body:
        if element.tag.endswith('p'):
            para = next((p for p in doc.paragraphs if p._element == element), None)
            if para:
                nivel = detectar_nivel_titulo(para)
                estilos = extrair_estilos(para)
                texto = para.text
                
                # Preserva blocos vazios se forem títulos (estrutura)
                if not texto.strip() and nivel == 0:
                    continue  # Pula parágrafos vazios sem significado
                
                # Detecta variáveis
                vars_para = detectar_variaveis(texto)
                vars_implicitas = detectar_variaveis_implicitas(texto)
                todas_vars = list(set(vars_para + vars_implicitas))
                variaveis_globais.update(todas_vars)
                
                # Classifica o bloco
                tipo_bloco = classificar_bloco(texto, estilos, nivel)
                
                blocos.append({
                    'tipo': 'texto',
                    'subtipo': tipo_bloco,
                    'conteudo': texto,
                    'estilos_css': estilos,
                    'variaveis': todas_vars,
                    'nivel_titulo': nivel
                })
                
        elif element.tag.endswith('tbl'):
            table = next((t for t in doc.tables if t._element == element), None)
            if table:
                tabela_proc = processar_tabela(table)
                texto_tabela = ' '.join([' '.join([c['texto'] for c in row]) for row in tabela_proc['linhas']])
                vars_tabela = detectar_variaveis(texto_tabela)
                vars_imp_tabela = detectar_variaveis_implicitas(texto_tabela)
                todas_vars_tabela = list(set(vars_tabela + vars_imp_tabela))
                variaveis_globais.update(todas_vars_tabela)
                tabela_proc['variaveis'] = todas_vars_tabela
                
                blocos.append(tabela_proc)
    
    # Gera CSS dinâmico baseado nos blocos processados
    css_dinamico = gerar_css_dinamico(blocos)
    
    # Gera HTML preservando formatação
    try:
        with open(caminho_docx, "rb") as docx_file:
            result_mammoth = mammoth.convert_to_html(docx_file)
            html_base = result_mammoth.value
    except:
        html_base = gerar_html_blocos(blocos, imagens)
    
    return {
        'sucesso': True,
        'nome_arquivo': os.path.basename(caminho_docx),
        'blocos': blocos,
        'total_blocos': len(blocos),
        'variaveis': sorted(list(variaveis_globais)),
        'total_variaveis': len(variaveis_globais),
        'imagens': len(imagens),
        'html_preview': html_base,
        'css_geral': css_dinamico
    }

def gerar_html_blocos(blocos, imagens):
    """Gera HTML preservando formatação original fallback"""
    html_parts = []
    
    for bloco in blocos:
        if bloco['tipo'] == 'texto':
            tag = 'p'
            classes = [bloco['subtipo']]
            
            if bloco['nivel_titulo'] == 1:
                tag = 'h1'
                classes.append('titulo-principal')
            elif bloco['nivel_titulo'] == 2:
                tag = 'h2'
                classes.append('titulo-secao')
            elif bloco['nivel_titulo'] == 3:
                tag = 'h3'
            
            estilos_inline = '; '.join([f"{k}:{v}" for k,v in bloco['estilos_css'].items()])
            conteudo = bloco['conteudo']
            
            # Substitui variáveis por placeholders visuais
            for var in bloco['variaveis']:
                conteudo = conteudo.replace(f"${{{var}}}", f'<span class="var-placeholder" data-var="{var}">{{{var}}}</span>')
                conteudo = conteudo.replace(f"{{{var}}}", f'<span class="var-placeholder" data-var="{var}">{{{var}}}</span>')
            
            html_parts.append(f'<{tag} class="{" ".join(classes)}" style="{estilos_inline}">{conteudo}</{tag}>')
            
        elif bloco['tipo'] == 'tabela':
            html_tbl = ['<table class="tabela-proposta" border="1" style="width:100%;border-collapse:collapse;margin:20px 0;">']
            for i, row in enumerate(bloco['linhas']):
                tag_row = 'th' if i == 0 else 'td'
                html_tbl.append('<tr>')
                for cell in row:
                    estilos = ';'.join([f"{k}:{v}" for k,v in cell['estilos'].items()])
                    texto_celula = cell["texto"].replace('\\n', '<br>')
                    html_tbl.append(f'<{tag_row} colspan="{cell["colspan"]}" style="{estilos}">{texto_celula}</{tag_row}>')
                html_tbl.append('</tr>')
            html_tbl.append('</table>')
            html_parts.append(''.join(html_tbl))
    
    return '\\n'.join(html_parts)

if __name__ == "__main__":
    # Configura o terminal para UTF-8 (Vital para Windows)
    if sys.platform == "win32":
        import io
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

    if len(sys.argv) > 1 and sys.argv[1] == '--test':
        print(json.dumps({
            "sucesso": True,
            "mensagem": "Python e dependências OK",
            "python_version": sys.version,
            "mammoth": "OK" if importlib.util.find_spec("mammoth") else "Faltando",
            "docx": "OK" if importlib.util.find_spec("docx") else "Faltando"
        }, ensure_ascii=False))
        sys.exit(0)

    if len(sys.argv) < 2:
        print(json.dumps({
            "sucesso": False,
            "erro": "Uso: python conversor_docx.py <arquivo.docx> ou --test"
        }, ensure_ascii=False))
        sys.exit(1)
    
    try:
        resultado = converter_docx(sys.argv[1])
        print(json.dumps(resultado, ensure_ascii=False, indent=2))
    except Exception as e:
        import traceback
        print(json.dumps({
            'erro': str(e), 
            'traceback': traceback.format_exc(),
            'sucesso': False
        }))
