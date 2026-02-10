<?php
// Arquivo: politica_privacidade.php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade | SGT Proposta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { background-color: #0F172A; color: #F8FAFC; font-family: 'Inter', sans-serif; }</style>
</head>
<body class="py-12 px-4 md:px-8">
    <div class="max-w-4xl mx-auto bg-[#1E293B] p-8 rounded-2xl border border-white/10 shadow-2xl">
        <h1 class="text-3xl font-bold text-white mb-2">Política de Privacidade</h1>
        <p class="text-slate-400 mb-8">Em conformidade com a LGPD (Lei 13.709/2018)</p>

        <div class="space-y-6 text-slate-300 leading-relaxed">
            <section>
                <h2 class="text-xl font-bold text-white mb-2">1. Coleta de Dados</h2>
                <p>A **ELM Topografia**, controladora do **SGT Proposta**, coleta dados estritamente necessários para o funcionamento do sistema:</p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-400">
                    <li>Dados de Cadastro: Nome, E-mail, Telefone (para login e suporte).</li>
                    <li>Dados de Uso: Logs de acesso e IP (para segurança e auditoria).</li>
                    <li>Dados de Propostas: Informações inseridas pelo usuário para gerar documentos.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-white mb-2">2. Armazenamento e Segurança</h2>
                <p>Os dados são armazenados em bancos de dados relacionais (MySQL) com criptografia em repouso e em trânsito (SSL). Utilizamos isolamento lógico (Tenant Isolation) para garantir que um usuário não acesse dados de outro.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-white mb-2">3. Compartilhamento</h2>
                <p>Não vendemos nem compartilhamos seus dados com terceiros para fins de marketing. O compartilhamento ocorre apenas com prestadores de serviço essenciais (ex: gateway de pagamento) ou por ordem judicial.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-white mb-2">4. Seus Direitos</h2>
                <p>Você pode solicitar a exclusão, correção ou exportação dos seus dados a qualquer momento entrando em contato com nosso suporte.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-white mb-2">5. Cookies</h2>
                <p>Utilizamos cookies apenas para manter sua sessão de login ativa (essenciais). Não utilizamos cookies de rastreamento publicitário.</p>
            </section>
        </div>

        <div class="mt-12 pt-8 border-t border-white/10 text-center">
            <a href="index.php" class="text-orange-500 hover:text-orange-400 font-bold">Voltar para a Home</a>
        </div>
    </div>
</body>
</html>
