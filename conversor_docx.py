#!/usr/bin/env python3
"""
Conversor DOCX -> Estrutura JSON para SGT Propostas
Preserva formatação, tabelas, imagens e detecta variáveis
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
                    'background': '#f8f9fa' if len(rows) == 0 else 'transparent', # Header background
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
    # Regex melhorada para suportar espaços opcionais
    padrao = r'\$\{\s*(\w+)\s*\}|\{\{\s*(\w+)\s*\}\}'
    matches = re.findall(padrao, texto)
    return list(set([m[0] or m[1] for m in matches if m[0] or m[1]]))

def processar_imagens(doc, docx_path):
    """Extrai imagens embutidas e converte para base64"""
    imagens = []
    
    # Relacionamentos do documento
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

def converter_docx(caminho_docx):
    """Função principal de conversão"""
    
    if not os.path.exists(caminho_docx):
        return {'sucesso': False, 'erro': f'Arquivo nao encontrado: {caminho_docx}'}

    doc = Document(caminho_docx)
    
    # Extrai imagens primeiro
    imagens = processar_imagens(doc, caminho_docx)
    
    blocos = []
    variaveis_globais = set()
    
    for element in doc.element.body:
        if element.tag.endswith('p'):  # Parágrafo
            para = next((p for p in doc.paragraphs if p._element == element), None)
            if para and para.text.strip():
                estilos = extrair_estilos(para)
                texto = para.text
                
                # Detecta variáveis
                vars_para = detectar_variaveis(texto)
                variaveis_globais.update(vars_para)
                
                # Detecta tipo de bloco por conteúdo
                tipo_bloco = classificar_bloco(texto, estilos)
                
                blocos.append({
                    'tipo': 'texto',
                    'subtipo': tipo_bloco,
                    'conteudo': texto,
                    'estilos_css': estilos,
                    'variaveis': vars_para,
                    'nivel_titulo': detectar_nivel_titulo(para)
                })
                
        elif element.tag.endswith('tbl'):  # Tabela
            table = next((t for t in doc.tables if t._element == element), None)
            if table:
                tabela_proc = processar_tabela(table)
                # Detecta variáveis em toda a tabela
                texto_tabela = ' '.join([' '.join([c['texto'] for c in row]) for row in tabela_proc['linhas']])
                vars_tabela = detectar_variaveis(texto_tabela)
                variaveis_globais.update(vars_tabela)
                tabela_proc['variaveis'] = vars_tabela
                
                blocos.append(tabela_proc)
    
    # Gera HTML preservando formatação (Usando Mammoth para melhor fidelidade se possível)
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
        'css_geral': gerar_css_padrao()
    }

def classificar_bloco(texto, estilos):
    """Classifica o bloco por conteúdo"""
    texto_lower = texto.lower()
    
    if any(x in texto_lower for x in ['cliente', 'contratante']):
        return 'dados_cliente'
    elif any(x in texto_lower for x in ['obra', 'local', 'terreno']):
        return 'dados_obra'
    elif any(x in texto_lower for x in ['valor', 'investimento', 'r$']):
        return 'valores'
    elif any(x in texto_lower for x in ['prazo', 'dias', 'data']):
        return 'prazos'
    elif any(x in texto_lower for x in ['pagamento', 'banco', 'pix']):
        return 'pagamento'
    elif estilos.get('font-weight') == 'bold' and len(texto) < 100:
        return 'titulo'
    else:
        return 'texto_geral'

def detectar_nivel_titulo(para):
    """Detecta se é H1, H2, H3 baseado no estilo do Word"""
    style_name = para.style.name if para.style else ''
    
    if 'Heading 1' in style_name or para.text.isupper():
        return 1
    elif 'Heading 2' in style_name:
        return 2
    elif 'Heading 3' in style_name:
        return 3
    return 0

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
                    # Preserva quebras de linha simples no texto da tabela
                    texto_celula = cell["texto"].replace('\n', '<br>')
                    html_tbl.append(f'<{tag_row} colspan="{cell["colspan"]}" style="{estilos}">{texto_celula}</{tag_row}>')
                html_tbl.append('</tr>')
            html_tbl.append('</table>')
            html_parts.append(''.join(html_tbl))
    
    return '\n'.join(html_parts)

def gerar_css_padrao():
    """CSS base que mantém consistência visual"""
    return """
        .modelo-docx { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 900px; margin: 0 auto; }
        .titulo-principal { color: #2563eb; border-bottom: 3px solid #2563eb; padding-bottom: 10px; }
        .titulo-secao { color: #1e40af; margin-top: 25px; }
        .var-placeholder { background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-family: monospace; border: 1px dashed #3b82f6; }
        .tabela-proposta th { background: #f1f5f9; font-weight: 600; }
        .dados_cliente, .dados_obra { background: #f8fafc; padding: 15px; border-radius: 8px; margin: 10px 0; }
    """

if __name__ == "__main__":
    # Configura o terminal para UTF-8 (Vital para Windows)
    if sys.platform == "win32":
        import io
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

    if len(sys.argv) > 1 and sys.argv[1] == '--test':
        # Modo de teste - retorna sucesso sem processar arquivo
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
        print(json.dumps({'erro': str(e), 'sucesso': False}))
