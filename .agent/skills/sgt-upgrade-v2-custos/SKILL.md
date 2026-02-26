---
name: sgt-upgrade-v2-custos
description: Protocolo para implementação da Edição Completa (v2.0) com persistência de planilhas de custos.
---

# 🚀 Skill: Upgrade SGT Propostas v2.0 (Custos Detalhados)

Esta skill define o protocolo para transformar a persistência de custos de "apenas IDs" para "detalhes completos de linha", permitindo uma edição estilo ScriptCase.

## 🎯 Objetivo

Habilitar a edição real da planilha de custos salvando quantitativos, marcas, valores unitários e subtotais diretamente em colunas "flat" na tabela `propostas`.

## 🛠️ Protocolo em 8 Passos

### 1. Migration SQL (250+ Colunas)

- **Regra**: Criar colunas para 5 linhas de Admin, 10 de Funções, 5 de Estadia, 20 de Consumo e 10 de Locação.
- **Padrão**: `[prefixo]_[indice]_[campo]` (ex: `adm_1_total`, `fun_3_dias`).
- **Segurança**: Usar `IF NOT EXISTS`.

### 2. PropostaRepository v2.0

- **CRUD Completo**: `salvarCompleto`, `buscarCompleto`, `atualizarCompleto`.
- **Transações**: Uso obrigatório de `beginTransaction`, `commit` e `rollBack`.
- **Compatibilidade**: Manter os campos de ID antigos (`tipo_admin_id`, etc) para não quebrar o legado.

### 3. Persistência (salvar_proposta.php)

- Delegar toda a lógica ao Repository.
- Validar CSRF e sessão.

### 4. Interface de Edição (editar_proposta.php)

- Injetar `window.SGT_DATA.planilhaExistente` com os dados do banco.
- Manter o Wizard de 4 passos.

### 5. Fluxo de Atualização (atualizar_proposta.php)

- Tratar redirecionamentos dinâmicos (`salvar_visualizar`, `salvar_editor`, etc).

### 6. Frontend Engine (costs-manager.js)

- Implementar `carregarPlanilhaCompleta(planilha)`.
- Criar métodos `adicionarLinha...ComDados(dados, indice)`.

### 7. Verificação (testar_crud_v2.php)

- Testes de integridade (Soma dos itens == Total Geral).
- Cobertura de Create, Read e Update.

### 8. Rollback (rollback_v2.php)

- Segurança via Token e confirmação textual.
- Remoção cirúrgica das novas colunas preservando dados básicos.

## 🛡️ Regras de Ouro

- **NÃO MEXER** no `criar_proposta.php`.
- **NÃO DIMINUIR** a estética SGT Dark Theme.
- **BACKUP OBRIGATÓRIO**: Antes de modificar qualquer arquivo vital, uma cópia deve ser salva no diretório `Backups/Fase2_Pre_Arquitetura/` ou similar com o sufixo `_bkp_[data]`.
- **LOGS**: Todo erro no Repository deve ser logado no sistema.
