---
trigger: always_on
---

# GEMINI.md - Regras do Agente (Restaurado)

Este arquivo define as regras de "personalidade" e comportamento da IA neste projeto.

---

## 🌎 1. REGRA DE IDIOMA (PRIORIDADE MÁXIMA)

**VOCÊ DEVE SEMPRE RESPONDER EM PORTUGUÊS DO BRASIL (PT-BR).**

* Não use inglês nas explicações.
* Traduza termos técnicos quando fizer sentido, ou explique-os.
* Se o usuário falar outra língua, responda nessa língua, mas o padrão é PT-BR.

---

## 🛡️ 2. PROTOCOLO DE SISTEMA LEGADO (OBRIGATÓRIO)

Como estamos lidando com um sistema existente (`SistemaWeb`), você deve seguir a skill `legacy-system-protocol` antes de qualquer alteração de código.

1. **Analise o terreno:** Onde estão os arquivos? Como é a sessão?
2. **Não quebre nada:** Verifique dependências (`config.php`, `db.php`) antes de criar novos arquivos.
3. **Pergunte antes:** Se tiver dúvida sobre uma função existente, pergunte ao usuário ou procure no código.

---

## 🛠️ 3. PADRÕES TÉCNICOS

* **PHP:** Prefira código compatível (ex: `switch` em vez de `match` se o servidor for antigo).
* **Front-end:** SGT Dark Theme (Glassmorphism, Inter font).
* **Segurança:** Sempre valide inputs (`filter_input`) e outputs (`htmlspecialchars`).

---

## 🛡️ 4. PROTOCOLO DE PRESERVAÇÃO E SEGURANÇA (Regras de Ferro - GLOBAL)

### 4.1. LEI DO "ANTI-DOWNGRADE" (Visual e Funcional)

* **Proibição Absoluta:** É estritamente proibido substituir componentes estilizados (SGT Dark Theme/Glassmorphism) por versões padrão ou "soluções prontas" genéricas.
* **Adaptação Obrigatória:** Se buscar uma solução externa, você é **OBRIGADO** a aplicar o CSS e a estrutura HTML do projeto ANTES de apresentar ou aplicar o código.

### 4.2. PROTOCOLO "PARE E REVERTA" (Anti-Colapso)

* **A Regra dos 2 Erros:** Se corrigir um erro gerar novos erros ou quebrar o layout: **PARE IMEDIATAMENTE**.

### 4.3. APENAS SOLUÇÕES COMPATÍVEIS

* Nunca implemente a "primeira solução que aparecer". Avalie se é compatível com PHP Legado/SGT Theme.

### 4.4. PROTOCOLO ANTI-REGRESSÃO (Cegueira de Contexto)

* **A Regra do Pós-Salvamento:** Nunca assuma que uma edição num arquivo isolado funcionou 100% sem checar as páginas chamadas por ele (Redirecionamentos, botões, includes).
* **Ação Obrigatória (Terminal):** Após qualquer edição de código pesado em arquivos PHP vitais (como *gerador_upload_docx.php* ou *editor_dinamico.php*), o agente **DEVE OBRIGATORIAMENTE** executar um teste local seco via terminal (ex: `curl -s -o /dev/null -w "%{http_code}\n" "link"`) para confirmar que a página não retornará Fatal Error/Tela Branca, antes de convidar o usuário para testar.
* **Proibição do "Túnel":** É proibido encerrar a tarefa sem inspecionar se a rota apontada pelos botões de sucesso/formulários estão corretas (ex: localhost vs painel.php).

---

## 🏗️ 5. REGRAS DE INFRAESTRUTURA (AMBIENTE REMOTO)

**ESTE PROJETO NÃO POSSUI AMBIENTE LOCAL. SIGA ESTAS REGRAS ESTRITAMENTE:**

1. **Servidor Remoto Único:** O projeto reside exclusivamente no domínio `https://elmtopografia.com.br/` na pasta `Orcamento`.
2. **Banco de Dados Externo:** A base de dados nunca é `localhost`. As configurações de IP e credenciais estão em `db.php`.
3. **Proibição de Conexão Direta:** A IA não consegue conectar-se diretamente ao IP do banco remoto devido a restrições de rede.
   * **Ação:** Para consultas ao banco, use scripts PHP de diagnóstico (como `diagnostico_tabelas.php`) enviados via SFTP para execução no servidor.
4. **Sincronização SFTP:** Todas as alterações locais são espelhadas instantaneamente para o servidor. Confie na leitura dos arquivos locais como fonte da verdade do servidor.
5. **Zero Latência Local:** Nunca tente rodar servidores PHP locais (Apache/Nginx/MySQL) para este projeto.
