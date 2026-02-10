# PROTOCOLO DE EDIÇÃO CIRÚRGICA

> "Ao editar um arquivo, nunca modifique ele todo, só a parte solicitada."

## Diretrizes

1. **Escopo Mínimo**: Alterar apenas as linhas estritamente necessárias para a tarefa.
2. **Preservação de Contexto**: Não reformatar, indentar ou "limpar" trechos de código que não estão relacionados ao bug ou feature atual.
3. **Proibição de Substituição Total**: Nunca usar `write_to_file` com `overwrite=true` para substituir um arquivo de código existente (exceto scripts novos). Usar sempre `replace_file_content` ou `multi_replace_file_content`.
4. **Respeito ao Legado**: Assumir que código estranho pode ter uma razão de ser. Não refatorar sem ordem expressa.
5. **Cuidado com Backups**: Nunca sobrescrever um arquivo saudável com um arquivo não testado (ex: baixado de outro repo) sem backup prévio.
