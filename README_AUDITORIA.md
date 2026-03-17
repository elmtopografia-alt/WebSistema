# Auditor de Modelos DOCX - SGT Propostas

Este script foi implantado para ajudar na transição e auditoria dos modelos de propostas DOCX. Ele analisa a estrutura real dos arquivos, mapeando campos, seções e categorias.

## 📁 Estrutura de Pastas
- `scripts/auditor_docx.php`: O script principal da auditoria.
- `modelos_docx_originais/`: Pasta onde você deve colocar os arquivos `.docx` que deseja auditar.
- `auditoria/`: Pasta onde os relatórios (JSON, HTML e LOG) serão gerados.

## 🚀 Como Executar

Abra o terminal na raiz do projeto e execute o seguinte comando:

```bash
php scripts/auditor_docx.php modelos_docx_originais/ auditoria/
```

### Se estiver usando um diretório específico (ex: modelos de produção):
```bash
php scripts/auditor_docx.php modelos_prod/ auditoria/
```

## 📊 O que analisar no Relatório HTML
Após a execução, abra o arquivo `auditoria/auditoria_docx_[DATA].html` no seu navegador para ver:

1. **Categorias Detectadas**: Agrupamento automático por tipo (Drone, Topografia, Obra, etc).
2. **Campos (Placeholders)**: Lista de todas as chaves detectadas (ex: `{{cliente}}`, `[DATA]`, etc).
3. **Frequência de Uso**: Identifique quais campos são obrigatórios (aparecem em 90%+) e quais são específicos.
4. **Seções Padrão**: Mapeamento de títulos e estrutura de blocos.

## 📋 Checklist de Transição
- [ ] Copiar os DOCX atuais para `modelos_docx_originais/`
- [ ] Rodar a auditoria
- [ ] Verificar campos não detectados ou nomes inconsistentes no HTML
- [ ] Validar se as categorias sugeridas fazem sentido para o novo sistema
