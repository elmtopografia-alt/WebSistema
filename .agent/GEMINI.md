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

## 🏗️ 5. REGRAS DE INFRAESTRUTURA (AMBIENTE REMOTO)

**ESTE PROJETO NÃO POSSUI AMBIENTE LOCAL. SIGA ESTAS REGRAS ESTRITAMENTE:**

1. **Servidor Remoto Único:** O projeto reside exclusivamente no domínio `https://elmtopografia.com.br/` na pasta `Orcamento`.
2. **Banco de Dados Externo:** A base de dados nunca é `localhost`. As configurações de IP e credenciais estão em `db.php`.
3. **Proibição de Conexão Direta:** A IA não consegue conectar-se diretamente ao IP do banco remoto devido a restrições de rede.
   * **Ação:** Para consultas ao banco, use scripts PHP de diagnóstico (como `diagnostico_tabelas.php`) enviados via SFTP para execução no servidor.
4. **Sincronização SFTP:** Todas as alterações locais são espelhadas instantaneamente para o servidor. Confie na leitura dos arquivos locais como fonte da verdade do servidor.
5. **Zero Latência Local:** Nunca tente rodar servidores PHP locais (Apache/Nginx/MySQL) para este projeto.
