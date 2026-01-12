# 🎬 Guia: Gerar Vídeo SGT Propostas com Google Veo 3.1

## 📍 Acesso ao Google Veo 3.1

### Opção 1: Google AI Studio (Recomendado para testes)
- **URL**: https://aistudio.google.com/
- **Vantagens**: Interface simples, gratuito para testes
- **Limitações**: Pode ter fila de espera

### Opção 2: Vertex AI (Para produção)
- **URL**: https://console.cloud.google.com/vertex-ai
- **Vantagens**: Mais recursos, maior controle
- **Requer**: Conta Google Cloud com billing ativo

---

## 🎯 Estratégia de Geração

O Veo 3.1 funciona melhor com **prompts descritivos detalhados**. Como seu vídeo tem 3 slides distintos, você tem **2 opções**:

### ✅ Opção A: Gerar 3 Vídeos Separados (Recomendado)
Gere cada slide como um vídeo separado e depois una no editor de vídeo.

### ⚠️ Opção B: Gerar 1 Vídeo Completo
Use um prompt único descrevendo toda a sequência (mais complexo, resultado menos previsível).

---

## 📝 Prompts Otimizados para Veo 3.1

### 🎬 Vídeo 1: Tela de Abertura (0-2.5s)

```
A professional corporate video opening shot. Modern landing page displayed on a large electronic screen. The screen shows the title "Gestão de Prosperidade" in bold, elegant typography. Below the title, there's a high-quality image of a professional surveyor/topographer operating a drone in an outdoor field. The SGT Propostas logo is visible in the corner. Clean, modern design with a blue and white color scheme. Smooth, subtle zoom-in camera movement. Professional lighting, corporate aesthetic, 16:9 format, high quality, cinematic.
```

**Configurações:**
- Duração: 3 segundos
- Aspect Ratio: 16:9
- Qualidade: Alta

---

### 📊 Vídeo 2: Dashboard Analítico (3-5.5s)

```
A vibrant, modern business dashboard displayed on a large screen. Colorful animated charts and graphs showing upward trends and positive performance metrics. Multiple data visualizations including line graphs, bar charts, and KPI indicators. Dynamic visual elements with smooth animations. Professional color palette with blues, greens, and oranges. Clean, modern UI design. Camera slowly pans across the dashboard. Corporate environment, professional lighting, 16:9 format, high quality, business intelligence aesthetic.
```

**Configurações:**
- Duração: 3 segundos
- Aspect Ratio: 16:9
- Qualidade: Alta

---

### 💰 Vídeo 3: Resultados Financeiros (6-8s)

```
A detailed financial table displayed on a professional screen. Clean spreadsheet-style layout showing monetary values in Brazilian Real (R$). Multiple rows with financial data, profit margins, and contract values. Professional color coding with green highlights for positive results. Modern, clean design. Subtle highlighting animation drawing attention to key numbers. Corporate aesthetic, professional lighting, 16:9 format, high quality, financial report style.
```

**Configurações:**
- Duração: 2 segundos
- Aspect Ratio: 16:9
- Qualidade: Alta

---

## 🎤 Adicionar Narração

O Veo 3.1 **não gera áudio automaticamente**. Você precisará:

### Opção 1: ElevenLabs (Recomendado)
- **URL**: https://elevenlabs.io/
- **Voz**: Escolha voz masculina em Português BR
- **Texto**: "Olá! Cansado de fazer propostas no papel? Transforme orçamentos em lucro real. O SGT organiza tudo e garante contratos fechados!"
- **Configuração**: Tom grave, estilo profissional/jornalístico

### Opção 2: Google Cloud Text-to-Speech
- **URL**: https://cloud.google.com/text-to-speech
- **Voz**: `pt-BR-Wavenet-B` (masculina, grave)
- **SSML**: Adicione ênfases para tom vendedor

### Opção 3: Gravar Você Mesmo
- Use um microfone de qualidade
- Ambiente silencioso
- Tom confiante e profissional

---

## 🎞️ Edição Final

Após gerar os 3 vídeos, use um editor para unir:

### Ferramentas Recomendadas:

1. **CapCut** (Gratuito, fácil)
   - Importe os 3 vídeos
   - Adicione transições fade (0.5s)
   - Adicione o áudio da narração
   - Música de fundo opcional (baixo volume)

2. **DaVinci Resolve** (Gratuito, profissional)
   - Mais controle sobre timing
   - Melhor sincronização áudio/vídeo

3. **Adobe Premiere Pro** (Pago, profissional)
   - Máximo controle criativo

---

## ⏱️ Timeline de Sincronização

| Tempo | Vídeo | Narração |
|-------|-------|----------|
| 0-2.5s | Slide 1 - Abertura | "Olá! Cansado de fazer propostas no papel?" |
| 2.5-3s | Transição Fade | - |
| 3-5.5s | Slide 2 - Dashboard | "Transforme orçamentos em lucro real. O SGT organiza tudo" |
| 5.5-6s | Transição Fade | - |
| 6-8s | Slide 3 - Financeiro | "e garante contratos fechados!" |

---

## 🎨 Dicas para Melhores Resultados

### ✅ Boas Práticas:
- Use prompts detalhados e específicos
- Mencione "16:9", "high quality", "professional"
- Especifique cores e estilo visual
- Gere múltiplas versões e escolha a melhor

### ❌ Evite:
- Prompts muito curtos ou vagos
- Pedir texto específico (Veo pode não renderizar bem)
- Movimentos de câmera muito complexos
- Transições dentro do mesmo vídeo

---

## 🚀 Passo a Passo Completo

1. **Acesse**: https://aistudio.google.com/
2. **Selecione**: Veo 3.1 (ou Veo 2 se 3.1 não disponível)
3. **Cole o Prompt 1**: Gere o vídeo da abertura
4. **Cole o Prompt 2**: Gere o vídeo do dashboard
5. **Cole o Prompt 3**: Gere o vídeo financeiro
6. **Gere o Áudio**: Use ElevenLabs ou similar
7. **Edite**: Una tudo no CapCut ou DaVinci Resolve
8. **Exporte**: MP4, 1920x1080, 30fps

---

## 📌 Recursos Adicionais

- **Veo Documentation**: https://ai.google.dev/
- **ElevenLabs**: https://elevenlabs.io/
- **CapCut**: https://www.capcut.com/
- **Música Corporativa Gratuita**: https://pixabay.com/music/

---

## ⚡ Atalho Rápido

**Copie e cole cada prompt diretamente no Google AI Studio, um de cada vez!**

Boa sorte com a produção do vídeo! 🎬✨
