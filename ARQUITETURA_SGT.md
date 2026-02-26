# ARQUITETURA SGT PROPOSTAS - VERSÃO DOCX V3

## 🚨 REGRAS INVIOLÁVEIS

### 1. Estrutura de Arquivos (NUNCA MUDE)
```text
crm-propostas/
├── editor_dinamico.php          ← EDITOR PRINCIPAL (SAGRADO)
├── salvar_proposta.php          ← API de persistência
├── gerar-proposta.php           ← Renderer HTML/PDF
├── models/
│   └── PropostaRepository.php   ← Padrão Repository
├── assets/
│   ├── css/tema-classico.css    ← Estilos base
│   └── js/editor-core.js        ← Lógica do editor
└── uploads/modelos/             ← DOCXs originais
```

### 2. Fluxo de Dados do Editor (MEMORIZE)
```text
[DOCX Upload]
↓
[Parser DOCX] → Extrai HTML + detecta {{variaveis}}
↓
[Editor Dinâmico] → Renderiza blocos editáveis
↓
[Usuário Edita] → Modifica conteúdo dos blocos
↓
[Salvar] → JSON dos blocos → Banco (campo conteudo_docx)
↓
[Gerar Proposta] → Parser HTML + CSS do Tema → PDF/DOCX
```

### 3. Estrutura do JSON no Banco

```json
{
  "modo": "docx_v3",
  "modelo_id": 5,
  "blocos": [
    {
      "id": "bloco_0",
      "tipo": "header",
      "conteudo": "<h1>Proposta...</h1>",
      "original": "{{titulo_proposta}}",
      "editado": true
    },
    {
      "id": "bloco_1", 
      "tipo": "secao",
      "titulo": "1. Escopo",
      "conteudo": "<p>Texto...</p>",
      "variaveis": ["{{nome_cliente}}", "{{area_total}}"]
    }
  ],
  "meta": {
    "data_geracao": "2026-02-23",
    "versao": "3.1"
  }
}
```

### 4. O Que NUNCA pode ser alterado no editor_dinamico.php
- ID do formulário: `id="formEditorDinamico"`
- Classe dos blocos: `class="docx-bloco-editable"`
- Atributo data: `data-bloco-id`, `data-bloco-tipo`
- Função JavaScript: `coletarBlocosParaSalvar()`
- Endpoint de salvamento: `action="salvar_proposta.php?modo=docx"`

### 5. Variáveis de Sessão Esperadas
```php
$_SESSION['proposta_draft_id']      // ID temporário
$_SESSION['modelo_docx_atual']      // ID do modelo DOCX
$_SESSION['modo_editor'] = 'docx'   // SEMPRE verificar
```

### 6. CSS Crítico (Não Remover)
```css
/* Áreas sagradas do editor */
#editor-container { }            /* Wrapper principal */
.bloco-docx-wrapper { }          /* Cada bloco do DOCX */
.editable-area { }               /* Contenteditable */
.preview-mode .editable-area { } /* Desativa edição */
```

### 7. Pontos de Extensão Permitidos
✅ **PODE adicionar:**
- Novos botões na toolbar (desde que não removam os existentes)
- Novos temas CSS (em arquivo separado)
- Validações JavaScript adicionais
- Campos hidden no formulário

❌ **NUNCA remova:**
- Estrutura de blocos dinâmicos
- Sistema de parse de variáveis `{{}}`
- Lógica de salvamento JSON
