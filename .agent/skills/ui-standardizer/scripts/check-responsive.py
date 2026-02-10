#!/usr/bin/env python3
"""
Script auxiliar para verificar problemas responsivos em arquivos CSS/JSX/PHP
Uso: python check-responsive.py [arquivo]
"""

import sys
import re
import os

# Fix Windows Console Encoding
if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8')

def analyze_file(filepath):
    issues = []
    
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
    except Exception as e:
        return [f"Erro ao ler arquivo: {str(e)}"]
    
    # 1. Verificar overflow hidden em containers de gráfico
    # Procura por overflow: hidden próximo a palavras chave
    if re.search(r'overflow:\s*hidden', content) and ('chart' in content.lower() or 'grafico' in content.lower() or 'canvas' in content.lower()):
        issues.append("⚠️  Possível clipping: 'overflow: hidden' detectado próximo a elementos de gráfico")
    
    # 2. Verificar altura fixa muito pequena
    heights = re.findall(r'height:\s*(\d+)px', content)
    for h in heights:
        if int(h) < 200:
            # Ignorar icones pequenos ou separadores se possível, mas alertar
            pass 
            # issues.append(f"ℹ️  Altura fixa pequena detectada ({h}px) - verificar se é container principal")

    # 3. Verificar cores claras em tema escuro (Hardcoded whites/light grays)
    # Procura por background: #fff, #ffffff, white, rgb(255,...)
    light_colors_hex = re.findall(r'background(?:-color)?:\s*(#[fF]{3}(?:[fF]{3})?)\b', content)
    light_colors_name = re.findall(r'background(?:-color)?:\s*(white)\b', content)
    
    if light_colors_hex or light_colors_name:
        count = len(light_colors_hex) + len(light_colors_name)
        issues.append(f"🎨 Cores claras hardcoded detectadas ({count} ocorrências) - verificar compatibilidade com Dark Theme (#0a0f1a)")
    
    # 4. Verificar falta de media queries (para CSS puro/style blocks)
    # Se o arquivo for grande (> 50 linhas) e não tiver @media ou classes tailwind (md:, lg:)
    if len(content.splitlines()) > 50:
        has_media = '@media' in content
        has_tailwind = re.search(r'\b(sm:|md:|lg:|xl:)', content)
        
        if not has_media and not has_tailwind:
            issues.append("📱 Nenhuma media query ou breakpoint Tailwind encontrado - verificar responsividade")

    # 5. Verificar larguras fixas grandes
    fixed_widths = re.findall(r'width:\s*(\d+)px', content)
    for w in fixed_widths:
        if int(w) > 320:
             issues.append(f"⚠️  width: {w}px fixo detectado. Use max-width ou porcentagem para mobile.")

    return issues

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Uso: python check-responsive.py <arquivo>")
        sys.exit(1)
    
    file_path = sys.argv[1]
    issues = analyze_file(file_path)
    
    if issues:
        print(f"\n🔍 Análise de {file_path}:")
        for issue in issues:
            print(f"  {issue}")
        print("\nSugestão: Use o skill [ui-standardizer] para corrigir estes pontos.")
    else:
        print(f"✅ {file_path} parece seguir boas práticas básicas!")
