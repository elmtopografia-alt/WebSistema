<?php
// Arquivo: termos_uso.php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso | SGT Proposta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { background-color: #0F172A; color: #F8FAFC; font-family: 'Inter', sans-serif; }</style>
</head>
<body class="py-12 px-4 md:px-8">
    <div class="max-w-4xl mx-auto bg-[#1E293B] p-8 rounded-2xl border border-white/10 shadow-2xl">
        <h1 class="text-3xl font-bold text-white mb-2">Termos de Uso</h1>
        <p class="text-slate-400 mb-8">Última atualização: <?= date('d/m/Y') ?></p>

        <div class="space-y-6 text-slate-300 leading-relaxed">
            <section>
                <h2 class="text-xl font-bold text-white mb-2">1. Aceitação</h2>
                <p>Ao acessar e utilizar o software **SGT Proposta** (doravante "SaaS"), oferecido por **ELM Topografia** (doravante "CONTRATADA"), você concorda com estes termos.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-white mb-2">2. Licença de Uso</h2>
                <p>A CONTRATADA concede uma licença revogável, não exclusiva e intransferível para uso do SaaS estritamente para gestão de propostas comerciais. É proibida a engenharia reversa, redistribuição ou cópia do código-fonte.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-white mb-2">3. Responsabilidades</h2>
                <p>O SaaS é fornecido "como está" (as is). A CONTRATADA não se responsabiliza por lucros cessantes, perda de dados decorrente de mau uso ou instabilidades de terceiros (hospedagem/internet).</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-white mb-2">4. Pagamento e Cancelamento</h2>
                <p>O serviço opera no modelo de assinatura (pré-pago). O não pagamento implica na suspensão imediata do acesso. O usuário pode cancelar a qualquer momento, sem multa, mantendo o acesso até o fim do ciclo pago.</p>
            </section>
            
            <section>
                <h2 class="text-xl font-bold text-white mb-2">5. Foro</h2>
                <p>Fica eleito o foro da comarca da sede da CONTRATADA para dirimir questões oriundas deste contrato.</p>
            </section>
        </div>

        <div class="mt-12 pt-8 border-t border-white/10 text-center">
            <a href="index.php" class="text-orange-500 hover:text-orange-400 font-bold">Voltar para a Home</a>
        </div>
    </div>
</body>
</html>
