# 🎓 Tutorial do Novo Sistema de Propostas (SGT)

Este guia rápido serve como sua "colinha" para usar o novo fluxo de geração de propostas, garantindo cálculos precisos e layouts bonitos.

---

## 🧵 Passo 1: Preparação Inicial (Apenas uma vez)

Se você percebeu mudanças no sistema ou se é a primeira vez usando a nova versão:

1. Abra o navegador.
2. Acesse o endereço: `http://localhost/SistemaWeb/run_install.php`
   *(Nota: Se o seu sistema estiver em um servidor online, troque "localhost" pelo domínio).*
3. Aguarde o carregamento. Se aparecerem mensagens em **Verde** (Módulo Instalado / Query OK), está tudo certo.
4. Feche a aba. Seu banco de dados está atualizado.

---

## 🚀 Passo 2: O Dia a Dia (Como Gerar uma Proposta)

Imagine que um cliente ligou agora solicitando um orçamento. Siga este roteiro:

### 1. Calculando (O Mestre dos Números) 🧮

Onde você faz as contas e define o preço.

* No **Painel**, clique em **"Criar Nova Proposta"**.
* Preencha os dados básicos (Cliente, Endereço da Obra).
* Escolha o serviço (ex: Topografia).
* Use a calculadora para adicionar dias de campo, equipe, alimentação, etc.
* **Novidade:** Ao terminar o cálculo, clique no botão **"Editar Layout"** (ou "Ir para Editor").

### 2. Editando (O Artista do Layout) 🎨

Onde você revisa o texto e deixa tudo bonito.

* Você cairá na tela do **Editor Dinâmico**.
* **Texto Automático:** O sistema já terá preenchido o "Escopo Técnico" com o texto padrão (ex: Planialtimétrico).
* **Valores:** O preço total calculado na etapa anterior já estará visível.
* **Sua Ação:** Leia o texto. Encontrou algo para mudar? Clique e edite como se fosse um Bloco de Notas.

### 3. Conferindo (A Segurança) 👁️

Antes de gerar o arquivo final.

* No topo da tela, clique no botão azul **"Visualizar"**.
* Uma nova aba abrirá mostrando como a proposta vai ficar no papel.
* Verifique se o nome do cliente está certo e se o layout está agradável.
* Se estiver ruim, feche a aba e edite mais um pouco.

### 4. Finalizando (A Entrega) 💾

* Tudo certo? Clique no botão verde **"Salvar / Emitir"**.
* O sistema vai:
    1. Salvar a proposta no banco de dados.
    2. Gerar o arquivo Word/PDF final.
    3. Salvar uma cópia automática na pasta do cliente (`/Ano/Mes/Cliente...`).
* Você será levado de volta ao Painel. Missão cumprida!

---

## 💡 Dicas de Ouro

* **Edição de Preço:** Se precisar mudar o valor, **não mude apenas o texto**. Clique no botão "Editar Cálculo" para o sistema ajustar a planilha interna. Assim, seus relatórios financeiros batem no fim do mês.
* **Salvamento:** O sistema salva rascunhos automaticamente enquanto você digita. Se acabar a luz, respire fundo e apenas abra a proposta novamente.
