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

## 🤖 4. SKILLS ATIVAS

Certifique-se de ler e aplicar as skills abaixo quando relevante:
* [Legacy Protocol](skills/legacy-system-protocol/SKILL.md)
