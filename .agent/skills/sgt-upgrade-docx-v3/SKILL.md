---
name: sgt-upgrade-docx-v3
description: Protocolo e checklist para migração do sistema SGT Propostas para DOCX v3.0.
---

# 🎯 Skill: Atualização SGT Propostas DOCX v3.0

Esta skill deve ser ativada durante toda a fase de migração para o suporte a modelos DOCX genéricos.

## 🚀 Objetivo

Migrar sistema SGT Propostas para suportar modelos DOCX genéricos e corrigir:

1. Nomes duplicados (hash + nome).
2. Falta de editor inline.
3. Incompatibilidade do `editor_dinamico.php`.
4. Persistência de blocos dinâmicos.

## 🛡️ Protocolo de Segurança (Regras de Ferro)

- **LEI DO ANTI-DOWNGRADE**: Nunca substitua componentes SGT Dark Theme/Glassmorphism por versões padrão.
- **REGRA DOS 2 ERROS**: Se um fix gerar novos erros ou quebrar layout, **PARE E REVERTA** (Ctrl+Z).
- **DIAGNÓSTICO PRIMEIRO**: Use logs/prints antes de qualquer alteração aleatória.

## 📁 Estrutura de Arquivos e Ações

| Arquivo | Ação |
| :--- | :--- |
| `gerador_upload_docx.php` | Substituir (Versão v3.0) |
| `editor_dinamico.php` | Substituir (Versão v3.0 com VariableResolver) |
| `salvar_proposta.php` | Substituir (Incluir extrairBlocosDocx) |
| `PropostaRepository.php` | Adicionar métodos `associarModeloDocx`, `salvarBlocosDocx`, `buscarBlocosDocx` |
| `verificador_saude_docx.php`| Criar novo (Verificação de integridade) |
| `Banco de Dados` | Executar `migracao_docx_v3.0.sql` |

## 🔧 Checklist de Implantação Mandatory

### 1. Preparação e Banco (Crítico)

- **Backup**:
  - DB: `mysqldump -u [USER] -p [DB] > backup.sql`
  - Arquivos: `tar -czvf backup_php.tar.gz ./`
- **Migração SQL**: Executar `migracao_docx_v3.0.sql`.
  - Criar `modelo_docx` (varchar)
  - Criar `docx_conteudo` (longtext)
  - Criar `docx_blocos_count` (int)
  - Criar `docx_ultima_edicao` (timestamp)

### 2. Migração PHP (Ordem de Execução)

1. **Upload**: `gerador_upload_docx.php` (v3.0 - sem hash no nome).
2. **Editor**: `editor_dinamico.php` (v3.0 - com Sidebar e mapeamento).
3. **Persistência**: `salvar_proposta.php` (v3.0 - extrairBlocosDocx).
4. **Repository**: Integrar novos métodos em `PropostaRepository.php`.

## 🧪 Testes de Aceitação (QA)

- [ ] **Novo DOCX**: Criar proposta -> Editor -> Selecionar Word -> Ver variáveis -> Salvar -> Visualizar.
- [ ] **Persistência**: Verificar se `docx_conteudo` no banco é um JSON válido após salvar.
- [ ] **Compatibilidade**: Abrir proposta antiga (Legacy) e confirmar que o editor original continua funcionando.

## 🚑 Plano de Rollback

Em caso de falha crítica:

1. **Banco**: `ALTER TABLE propostas DROP COLUMN modelo_docx, docx_conteudo, docx_blocos_count, docx_ultima_edicao;`
2. **Arquivos**: Restaurar o backup `.tar.gz`.
3. **Verificação**: Executar `verificador_saude_docx.php` para confirmar status anterior.
