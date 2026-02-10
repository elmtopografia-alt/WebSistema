---
name: legacy-system-protocol
description: Protocolo obrigatório para análise de sistemas legados antes de codificar.
---

# Protocolo de Análise de Sistema Legado

## REGRA OBRIGATÓRIA - ANÁLISE ANTES DE CODAR

Antes de escrever qualquer código em sistemas existentes, você DEVE seguir rigorosamente este fluxo:

1. **Mapeamento de Estrutura**
    * Perguntar onde estão os arquivos do projeto (estrutura de pastas).
    * Listar e entender a organização atual antes de propor mudanças.

2. **Identificação de Padrões**
    * Identificar como funciona o login/sessão atual do sistema (`session_start`, cookies, tokens?).
    * Identificar como funciona a conexão com banco de dados (PDO, MySQLi, Singleton?).

3. **Verificação de Existência**
    * Verificar se já existe algo relacionado ao que será pedido (tabelas, páginas, funções).
    * EM CASO DE DÚVIDA, use ferramentas de busca (`find_by_name`, `grep_search`) para encontrar códigos similares.

4. **Validação com Usuário**
    * Mostrar o que encontrou.
    * AGUARDAR a confirmação do usuário antes de prosseguir.

5. **Proposta Específica**
    * Só então propor a solução específica para ESTE sistema.
    * NÃO crie templates genéricos.
    * NÃO assuma que é um sistema novo.
    * NÃO ignore o que já existe.

## Protocolo de Erros

* **Erro 500 / Tela Branca**: PARE IMEDIATAMENTE.
* Não tente "adivinhar" o conserto.
* Peça para ver os logs do servidor (`error_log`, etc) ou use scripts de verificação de sintaxe (`php -l`).
