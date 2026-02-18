# GUIA CSS COMPLETO - Proposta Drone

## Resumo Rápido para Consulta

| Elemento | Classe Principal | Cor Destaque |
|---|---|---|
| Header | `.topo-institucional` | Border-bottom azul |
| Título | `.titulo-principal` | Azul `#2563eb` |
| Container | `.container-proposta` | Fundo cinza `#f8fafc` |
| Seções | `.secao-container` | Header gradiente azul claro |
| Botão Primário | `.btn-primario` | Azul `#2563eb` |
| Badge | `.secao-badge` | Azul arredondado |
| Valor Total | `.valor-total` | Azul + border-top |

## Links de Arquivos

- **HTML Protótipo:** `Modelo_Drone_Ref.html`
- **CSS Oficial:** `assets/css/proposta-drone.css`
- **Template Dinâmico:** `proposta_drone_novo.php`

## Dicas de Implementação

Se você precisar replicar este estilo em outras propostas:

1. Copie apenas as variáveis `:root` para o novo arquivo.
2. Mantenha a estrutura HTML com as mesmas classes.
3. Altere apenas:
   - Ícones nos `::before` (drone → outro emoji)
   - Títulos das seções
   - Conteúdo dos campos

## Como Usar Este CSS

**Opção 1: Arquivo Externo (Recomendado)**
Salve o código como `proposta-drone.css` e linke no HTML/PHP:

```html
<link rel="stylesheet" href="assets/css/proposta-drone.css">
```

## Checklist de Classes no HTML

Para garantir que tudo funcione, seu HTML deve ter:

- [ ] `<header class="topo-institucional">`
- [ ] `<div class="logo-placeholder">` ou `<img class="logo-img">`
- [ ] `<div class="container-proposta">`
- [ ] `<div class="proposta-header">`
- [ ] 7x `<div class="secao-container">` com:
  - [ ] `<div class="secao-header">`
  - [ ] `<h3 class="secao-titulo [classe-especifica]">` (ex: dados-cliente)
  - [ ] `<div class="secao-body">`
- [ ] `<div class="acoes-container">` com botões `.btn`
