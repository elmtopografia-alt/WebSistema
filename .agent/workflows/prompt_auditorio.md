---
description: Gera um prompt otimizado e rígido para manter a consistência do auditório ao trocar a tela.
---

1. **Coletar Informações**: Pergunte ao usuário:
   - Qual é o conteúdo que deve aparecer na tela/lousa? (Ex: Gráfico de vendas, Logo da empresa, Foto de um produto).
   - (Opcional) Se ele quer mudar o ângulo da câmera (Ex: Vista da plateia, Vista lateral).

2. **Gerar o Prompt**: Com base na resposta, monte o prompt usando o modelo "Blindado":
   
   ```text
   ATUE COMO UM EDITOR DE FOTOS (IMAGE COMPOSITOR).
   
   Tarefa: Inserir o conteúdo descrito abaixo na tela do auditório, mantendo o restante da imagem INALTERADO.
   
   Conteúdo da Tela: [INSERIR O QUE O USUÁRIO PEDIU AQUI]
   
   Protocolo de Rigidez:
   1. CONGELAR AMBIENTE: Mantenha 100% dos pixels do auditório inalterados (cadeiras, luz, pessoas).
   2. SUBSTITUIÇÃO EXATA: Apenas a área da tela deve ser alterada.
   3. PERSPECTIVA: Ajuste a imagem inserida para a perspectiva da parede.
   ```

   **Variação: Close-Up (Se o usuário pedir para ver melhor/aproximar):**
   ```text
   ATUE COMO UM EDITOR DE FOTOS.
   
   Tarefa: Gerar um CLOSE-UP (Zoom) da tela do auditório.
   
   Instruções:
   1. ENQUADRAMENTO: Corte a imagem para mostrar apenas a tela e uma pequena margem da parede/palco ao redor.
   2. CONTEÚDO: Insira a imagem [DESCRIÇÃO] na tela com alta nitidez.
   3. RESOLUÇÃO: Garanta que os textos e gráficos na tela estejam legíveis.
   ```

3. **Entregar**: Apresente o prompt final dentro de um bloco de código para o usuário copiar.
