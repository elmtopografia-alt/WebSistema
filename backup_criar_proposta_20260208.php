<?php

/**
 * ARQUIVO: criar_proposta.php
 * VERSÃO: Premium Wizard Mobile-First Optimized
 * Performance: Lazy loading, PWA-ready, Touch-optimized
 */

require_once 'session_validator.php';
require_once 'config.php';
require_once 'db.php';
// require_once 'valida_demo.php'; // Temporariamente desativado para teste

$id_usuario = $_SESSION['usuario_id'] ?? 0;
if (!$id_usuario) {
    header('Location: login.php');
    exit;
}

// Debug temporário (remover em produção se necessário, mas essencial agora)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==================== CACHE E OTIMIZAÇÃO ====================
$cache_key = "proposta_dados_{$id_usuario}";
$dados_cache = false;

// Verifica se APCu está disponível
if (function_exists('apcu_fetch')) {
    $dados_cache = apcu_fetch($cache_key);
}

if ($dados_cache === false) {
    try {
        // Seleciona conexão correta baseada no ambiente
        $ambiente = $_SESSION['ambiente'] ?? 'producao';
        $conn = ($ambiente === 'demo') ? Database::getDemo() : Database::getProd();

        // Queries otimizadas com índices
        $stmt_cli = $conn->prepare("
            SELECT id_cliente, nome_cliente, telefone, celular 
            FROM Clientes 
            WHERE id_criador = ? 
            ORDER BY nome_cliente ASC
            LIMIT 500
        ");
        $stmt_cli->bind_param('i', $id_usuario);
        $stmt_cli->execute();
        $clientes_res = $stmt_cli->get_result();

        // Arrays para JS - carregamento lazy
        $arrays_js = [];
        $tabelas = [
            'Tipo_Servicos' => ['id' => 'id_servico', 'nome' => 'nome', 'extra' => 'descricao'],
            'Tipo_Funcoes' => ['id' => 'id_funcao', 'nome' => 'nome', 'valor' => 'salario_base_default'],
            'Tipo_Estadia' => ['id' => 'id_estadia', 'nome' => 'nome', 'valor' => 'valor_unitario_default'],
            'Tipo_Consumo' => ['id' => 'id_consumo', 'nome' => 'nome', 'litro' => 'valor_litro_default', 'kml' => 'consumo_kml_default'],
            'Tipo_Locacao' => ['id' => 'id_locacao', 'nome' => 'nome', 'valor' => 'valor_mensal_default'],
            'Tipo_Custo_Admin' => ['id' => 'id_custo_admin', 'nome' => 'nome', 'valor' => 'valor_default']
        ];

        foreach ($tabelas as $tabela => $cols) {
            $result = $conn->query("SELECT * FROM {$tabela} ORDER BY nome ASC");
            $arrays_js[$tabela] = [];
            while ($row = $result->fetch_assoc()) {
                $item = ['id' => $row[$cols['id']], 'nome' => $row[$cols['nome']]];
                if (isset($cols['extra'])) $item['descricao'] = $row[$cols['extra']];
                if (isset($cols['valor'])) $item['valor'] = (float)$row[$cols['valor']];
                if (isset($cols['litro'])) $item['litro'] = (float)$row[$cols['litro']];
                if (isset($cols['kml'])) $item['kml'] = (float)$row[$cols['kml']];
                $arrays_js[$tabela][] = $item;
            }
        }

        // Estados
        $estados = [];
        $result = $conn->query("SELECT sigla, nome FROM estados ORDER BY nome ASC");
        while ($row = $result->fetch_assoc()) $estados[] = $row;

        // Marcas agrupadas
        $marcas_por_tipo = [];
        $result = $conn->query("SELECT id_marca, id_locacao, nome_marca FROM Marcas ORDER BY nome_marca ASC");
        while ($row = $result->fetch_assoc()) {
            $marcas_por_tipo[$row['id_locacao']][] = [
                'id' => $row['id_marca'],
                'nome' => $row['nome_marca']
            ];
        }

        // Endereço empresa
        $empresa_endereco = '';
        $stmt_emp = $conn->prepare("SELECT Endereco, Cidade, Estado FROM DadosEmpresa WHERE id_criador = ? LIMIT 1");
        $stmt_emp->bind_param('i', $id_usuario);
        $stmt_emp->execute();
        $res_emp = $stmt_emp->get_result();
        if ($row = $res_emp->fetch_assoc()) {
            $empresa_endereco = implode(', ', array_filter([$row['Endereco'], $row['Cidade'], $row['Estado']]));
        }

        // Recupera clientes como array
        $clientes = [];
        while ($row = $clientes_res->fetch_assoc()) {
            $clientes[] = $row;
        }

        $dados_cache = [
            'clientes' => $clientes,
            'arrays_js' => $arrays_js,
            'estados' => $estados,
            'marcas' => $marcas_por_tipo,
            'empresa_endereco' => $empresa_endereco,
            'timestamp' => time()
        ];

        // Cache por 5 minutos (se APCu disponível)
        if (function_exists('apcu_store')) {
            apcu_store($cache_key, $dados_cache, 300);
        }
    } catch (Exception $e) {
        error_log("Erro criar_proposta: " . $e->getMessage());
        die("Erro ao carregar dados: " . $e->getMessage());
    }
} else {
    // Extrai dados do cache se existir
    extract($dados_cache);
}

// Garante que as variáveis existam mesmo se o cache falhar de forma estranha
$clientes = $clientes ?? [];
$tipos_funcao = $arrays_js['Tipo_Funcoes'] ?? [];
$tipos_estadia = $arrays_js['Tipo_Estadia'] ?? [];
$tipos_consumo = $arrays_js['Tipo_Consumo'] ?? [];
$tipos_locacao = $arrays_js['Tipo_Locacao'] ?? [];
$tipos_admin = $arrays_js['Tipo_Custo_Admin'] ?? [];
$servicos = $arrays_js['Tipo_Servicos'] ?? [];

// Limpa variáveis de sessão que possam estar preenchendo o formulário
unset($_SESSION['proposta_dados']);
unset($_SESSION['erros']);
// unset($_POST['id_cliente']); // Removido para manter a persistencia do Wizard
// unset($_GET['id_cliente']); // Removido para manter a persistencia do Wizard
// Adicione outras chaves se necessário

// Gera CSRF token seguro
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="format-detection" content="telephone=no">

    <title>Nova Proposta | SGT Premium</title>

    <!-- Preconnect para performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <!-- CSS Crítico Inline (Above the fold) -->
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-body: #fafbfc;
            /* Branco quebrado sutil */
            --card-bg: #ffffff;
            --border-color: #94a3b8;
            /* Borda mais escura e definida (Slate-400) */
            --touch-min: 44px;
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.05);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.05);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-body);
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding-bottom: calc(80px + var(--safe-bottom));
            -webkit-font-smoothing: antialiased;
        }

        /* Mobile-First Navbar */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(255, 255, 255, 0.8);
            /* Mais transparecia */
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Wizard Container - Mobile First */
        .wizard-wrapper {
            max-width: 100%;
            margin: 0 auto;
            background: var(--card-bg);
            min-height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        @media (min-width: 768px) {
            .wizard-wrapper {
                max-width: 900px;
                margin: 1rem auto;
                border-radius: 16px;
                box-shadow: var(--shadow-md);
                border: 1px solid var(--border-color);
                min-height: auto;
            }

            /* Hover Elegante no Desktop */
            .wizard-wrapper:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
            }
        }

        @media (min-width: 1200px) {
            .wizard-wrapper {
                max-width: 1100px;
            }
        }

        /* Progress Steps - Mobile Dots / Desktop Labels */
        .wizard-progress {
            background: rgba(241, 245, 249, 0.5);
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 300px;
            max-width: 600px;
            margin: 0 auto;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            flex: 1;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 14px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #cbd5e1;
            z-index: 0;
        }

        .step.completed::after {
            background: var(--success);
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
        }

        .step.completed .step-number {
            background: var(--success);
            color: white;
        }

        .step-label {
            font-size: 0.625rem;
            color: var(--secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        @media (max-width: 480px) {
            .step-label {
                display: none;
            }

            .step.active .step-label {
                display: block;
                font-size: 0.75rem;
            }
        }

        /* Content Area */
        .wizard-content {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
        }

        @media (min-width: 768px) {
            .wizard-content {
                padding: 2rem;
            }
        }

        .step-panel {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .step-panel.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Typography Mobile */
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        .section-subtitle {
            color: var(--secondary);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .section-title {
                font-size: 1.5rem;
            }

            .section-subtitle {
                font-size: 1rem;
            }
        }

        /* Form Elements - Touch Optimized */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            /* Aumentei um pouco o peso também */
            font-size: 0.875rem;
            color: #1e293b;
            /* Slate 800 - Mais escuro que o anterior #334155 */
            margin-bottom: 0.375rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            min-height: var(--touch-min);
            padding: 0.625rem 0.875rem;
            font-size: 16px;
            /* Prevents zoom on iOS */
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background: white;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            -webkit-appearance: none;
            appearance: none;
            color: #0f172a;
            /* Slate 900 - Texto bem escuro */
            font-weight: 500;
            /* Texto mais 'gordinho'/definido */
        }

        @media (min-width: 768px) {

            .form-control,
            .form-select {
                font-size: 0.9375rem;
            }
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Input Group Fix */
        .input-group {
            display: flex;
            align-items: stretch;
            width: 100%;
        }

        .input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            flex: 1;
            min-width: 0;
            /* Prevent flex overflow */
        }

        .input-group .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            margin-left: -1px;
            z-index: 2;
        }

        /* Ajuste específico para quando tem mais de um botão */
        .input-group .btn:not(:last-child):not(:first-child) {
            border-radius: 0;
        }

        .input-group .btn:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        /* Grid responsiva */
        .form-row {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 576px) {
            .form-row.cols-2 {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-row.cols-3 {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 768px) {
            .form-row.cols-4 {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Cost Cards - Mobile Optimized */
        .cost-tabs {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .cost-tabs::-webkit-scrollbar {
            display: none;
        }

        .cost-tab {
            flex-shrink: 0;
            padding: 0.625rem 1rem;
            border-radius: 9999px;
            border: 1px solid var(--border-color);
            background: white;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--secondary);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        /* Custom Grid for Fuel (Combustível) */
        @media (min-width: 768px) {
            .cost-details-fuel {
                display: grid;
                /* FIXO: Força a largura dos campos menores para garantir que fiquem pequenos */
                /* Combustível(110px) Qtd(80px) Km/L(80px) R$/L(80px) KM(Restante) */
                grid-template-columns: 110px 80px 80px 80px 1fr !important;
                gap: 0.5rem;
                align-items: flex-end;
                width: 100%;
            }

            .cost-details-fuel>div {
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
            }
        }

        /* Small Buttons for KM Field */
        .btn-km-small {
            padding: 0 0.75rem !important;
            height: 38px !important;
            /* Altura do input padrão */
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            /* Não encolhe */
            min-width: 40px;
            /* Garante tamanho clicável */
        }

        /* Summary Grid Steps 4 */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 1rem;
        }

        @media (min-width: 992px) {
            .summary-grid {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        .summary-col {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .summary-header {
            text-align: center;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
        }

        /* Step Actions Bar */
        .step-actions {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #64748b;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .btn-action-calc {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-color: transparent;
        }

        .btn-action-calc:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
        }

        /* Calculator Modal */
        .calculator-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .calculator-modal.show {
            display: flex;
        }

        .calculator-box {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 320px;
            overflow: hidden;
            animation: fadeIn 0.2s ease;
        }

        .calculator-header {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .calculator-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .calculator-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            line-height: 1;
            opacity: 0.8;
        }

        .calculator-close:hover {
            opacity: 1;
        }

        .calculator-display {
            background: #0f172a;
            color: #22c55e;
            font-family: 'Courier New', monospace;
            font-size: 1.75rem;
            padding: 1rem;
            text-align: right;
            min-height: 60px;
            word-break: break-all;
        }

        .calculator-buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            background: #e2e8f0;
        }

        .calc-btn {
            background: white;
            border: none;
            padding: 1rem;
            font-size: 1.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.1s;
        }

        .calc-btn:hover {
            background: #f1f5f9;
        }

        .calc-btn:active {
            background: #e2e8f0;
        }

        .calc-btn-operator {
            background: #f8fafc;
            color: #3b82f6;
        }

        .calc-btn-equals {
            background: #22c55e;
            color: white;
        }

        .calc-btn-equals:hover {
            background: #16a34a;
        }

        .calc-btn-clear {
            background: #ef4444;
            color: white;
        }

        .calc-btn-clear:hover {
            background: #dc2626;
        }

        .summary-box-display {
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            height: 38px;
            /* Match input height */
            display: flex;
            align-items: center;
        }

        .final-val-box {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-top: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .final-val-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
            display: block;
        }

        /* Dashboard Style Layout (V2) - EXACT MATCH */
        .closing-section {
            background: #fff;
            padding: 0;
        }

        /* Resumo de Custos Box */
        .cost-summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
        }

        .cost-summary-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .cost-summary-details {
            margin-bottom: 0.75rem;
        }

        .cost-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
            font-size: 0.9rem;
        }

        .cost-summary-row span:first-child {
            color: #64748b;
        }

        .cost-summary-row span:last-child {
            font-weight: 600;
            color: #334155;
        }

        .cost-summary-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem;
            margin-top: 0.5rem;
            border-top: 2px solid #e2e8f0;
            font-size: 0.95rem;
        }

        .cost-summary-total-row span:first-child {
            color: #64748b;
            font-weight: 500;
        }

        .text-red-bold {
            color: #ef4444;
            font-weight: 700;
        }

        /* Margem e Desconto */
        .margin-section {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .margin-input-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .margin-input-wrapper input {
            flex: 1;
        }

        .margin-input-wrapper .percent-label {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            color: #64748b;
        }

        .margin-value-display {
            text-align: right;
            color: #10b981;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        /* Label Padrão - Estilo unificado para todos os campos */
        .label-padrao {
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Condições de Pagamento - Grid 2x2 */
        .payment-conditions-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 1rem;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 1rem 1.25rem;
        }

        .payment-input-group {
            margin-bottom: 0.25rem;
        }

        .payment-input-group label {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.35rem;
            display: block;
        }

        .payment-input-group input {
            font-size: 0.95rem;
            padding: 0.5rem 0.75rem;
        }

        .payment-input-group .input-readonly {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        .restante-input-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .restante-input-group input {
            width: 70px;
            text-align: center;
        }

        .restante-input-group .percent-text {
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Final Value Container */
        .final-value-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding-top: 2rem;
        }

        .big-total-label {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .big-total-value {
            font-size: 3rem;
            font-weight: 700;
            color: #2563eb;
            line-height: 1;
        }

        @media (max-width: 768px) {
            .big-total-value {
                font-size: 2.25rem;
            }
        }

        /* Divider */
        .divider-horizontal {
            height: 1px;
            background-color: #e2e8f0;
            margin: 1.5rem 0;
        }

        /* Save Button */
        .btn-save-review {
            background-color: #16a34a;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-save-review:hover {
            background-color: #15803d;
        }

        /* Grid for Step 4 - EXACT PROPORTIONS */
        .step-4-grid-top {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .step-4-grid-bottom {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: center;
        }

        @media (min-width: 768px) {
            .step-4-grid-top {
                grid-template-columns: 45fr 55fr;
                /* 45% - 55% */
            }

            .step-4-grid-bottom {
                grid-template-columns: 55fr 45fr;
                /* 55% - 45% */
            }
        }

        .cost-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .cost-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .cost-item {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.75rem;
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            /* Mobile friendly */
        }

        .cost-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            background: white;
        }

        /* Layout Grid para os inputs de custo */
        .cost-details {
            display: grid;
            grid-template-columns: 1fr;
            /* Mobile stack */
            gap: 0.5rem;
            flex: 1;
            width: 100%;
        }

        @media (min-width: 768px) {
            .cost-item {
                flex-wrap: nowrap;
            }

            .cost-details {
                grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
                /* 5 colunas padrão */
                align-items: flex-end;
                /* Alinha labels embaixo se houver variação */
            }

            /* Ajustes finos para rótulos dentro do grid */
            .cost-details>div {
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
            }
        }

        .cost-item-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .cost-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .cost-title {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9375rem;
        }

        .cost-grid {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 576px) {
            .cost-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 768px) {
            .cost-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }
        }

        .cost-total-mobile {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            border-top: 1px solid var(--border-color);
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary);
        }

        @media (min-width: 768px) {
            .cost-total-mobile {
                display: none;
            }
        }

        .btn-remove {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: #fee2e2;
            color: var(--danger);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: var(--touch-min);
            padding: 0.625rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
        }

        .btn-outline {
            background: white;
            border: 1.5px solid var(--border-color);
            color: var(--secondary);
        }

        .btn-add {
            width: 100%;
            border-style: dashed;
            border: 2px dashed #cbd5e1;
            background: #f8fafc;
            color: var(--secondary);
            margin-top: 0.75rem;
        }

        .btn-add:active {
            transform: scale(0.98);
        }

        /* Footer Actions - Fixed Mobile */
        .wizard-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid var(--border-color);
            padding: 0.75rem 1rem calc(0.75rem + var(--safe-bottom));
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            z-index: 100;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        @media (min-width: 768px) {
            .wizard-footer {
                position: static;
                padding: 1.5rem 2rem;
                box-shadow: none;
                border-top: 1px solid var(--border-color);
                border-radius: 0 0 16px 16px;
            }
        }

        .wizard-footer .btn {
            flex: 1;
        }

        @media (min-width: 768px) {
            .wizard-footer .btn {
                flex: 0 0 auto;
                min-width: 140px;
            }
        }

        /* Summary Cards */
        .summary-card {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.9375rem;
        }

        .summary-row:not(:last-child) {
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-total {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--danger);
        }

        .final-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            text-align: center;
            margin: 1rem 0;
        }

        @media (min-width: 768px) {
            .final-value {
                font-size: 2.5rem;
            }
        }

        /* Loading States */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: calc(100% - 2rem);
        }

        .toast {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideInRight 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .toast.success {
            border-color: var(--success);
        }

        .toast.error {
            border-color: var(--danger);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Select2 Mobile Optimization */
        .select2-container {
            width: 100% !important;
        }

        .select2-selection {
            min-height: var(--touch-min) !important;
            padding: 0.375rem 0.75rem !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 10px !important;
        }

        .select2-selection__rendered {
            line-height: 1.5 !important;
            padding-left: 0 !important;
            color: #0f172a !important;
            font-weight: 500 !important;
        }

        .select2-selection__arrow {
            height: var(--touch-min) !important;
        }

        /* Modal Mobile Fullscreen */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1050;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal.show {
            display: flex;
            align-items: flex-end;
        }

        @media (min-width: 576px) {
            .modal.show {
                align-items: center;
                justify-content: center;
            }
        }

        .modal-dialog {
            background: white;
            width: 100%;
            max-height: 90vh;
            border-radius: 20px 20px 0 0;
            overflow: hidden;
            animation: slideUp 0.3s ease;
        }

        @media (min-width: 576px) {
            .modal-dialog {
                width: 90%;
                max-width: 600px;
                border-radius: 16px;
                animation: fadeIn 0.3s ease;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 1rem;
            background: var(--success);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 1rem;
            max-height: 60vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 0.75rem;
        }

        .modal-footer .btn {
            flex: 1;
        }

        /* Utility */
        .hidden {
            display: none !important;
        }

        .text-danger {
            color: var(--danger);
        }

        .text-success {
            color: var(--success);
        }

        .text-muted {
            color: var(--secondary);
        }

        .fw-bold {
            font-weight: 700;
        }

        .d-flex {
            display: flex;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 0.75rem;
        }

        .flex-1 {
            flex: 1;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        @keyframes aurora {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.03;
            }

            33% {
                transform: translate(30px, -30px) scale(1.1);
                opacity: 0.05;
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
                opacity: 0.02;
            }
        }

        .ambient-glow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .ambient-glow::before,
        .ambient-glow::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.03;
            animation: aurora 20s ease infinite;
        }

        .ambient-glow::before {
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, #2563eb 0%, transparent 70%);
            top: -20%;
            right: -20%;
            animation-delay: 0s;
        }

        .ambient-glow::after {
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, #f59e0b 0%, transparent 70%);
            bottom: -10%;
            left: -10%;
            animation-delay: -10s;
        }

/* ============================================
   CORREÇÕES MOBILE - KIMI (CRIAR_PROPOSTA.PHP)
   ============================================ */

/* Wrapper do seletor de cliente - layout flexível */
.cliente-selector-wrapper {
    display: flex;
    gap: 0.5rem;
    align-items: stretch;
}

.cliente-selector-wrapper .form-select {
    flex: 1;
    min-width: 0;
}

.btn-novo-cliente {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0 1rem;
    min-height: 44px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* Mobile: botão mais compacto */
@media (max-width: 576px) {
    .btn-novo-cliente {
        padding: 0 0.75rem;
        min-width: 44px;
    }
    
    .btn-novo-cliente .btn-text-desktop {
        display: none;
    }
}

@media (min-width: 577px) {
    .btn-novo-cliente .btn-text-desktop {
        display: inline;
    }
}

/* Grid de endereço responsivo */
.address-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.address-field {
    display: flex;
    flex-direction: column;
}

.address-field-large { flex: 2; min-width: 280px; }
.address-field-medium { flex: 0.8; min-width: 140px; }
.address-field-small { flex: 0.6; min-width: 120px; }
.address-field-tiny { flex: 0.6; min-width: 100px; }

@media (max-width: 768px) {
    .address-row {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .address-field {
        width: 100% !important;
        min-width: auto !important;
        flex: none !important;
    }
}

/* ============================================
   MODAL MOBILE CORRIGIDO
   ============================================ */

.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9998;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.show {
    display: block;
    opacity: 1;
}

.modal-dialog-custom {
    display: none;
    position: fixed;
    z-index: 9999;
    background: white;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    overflow: hidden;
    animation: modalSlideUp 0.3s ease;
}

@media (min-width: 577px) {
    .modal-dialog-custom {
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
    }
}

@media (max-width: 576px) {
    .modal-dialog-custom {
        top: auto;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        max-height: 95vh;
        border-radius: 20px 20px 0 0;
        animation: modalSlideUpMobile 0.3s ease;
    }
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translate(-50%, -45%); }
    to { opacity: 1; transform: translate(-50%, -50%); }
}

@keyframes modalSlideUpMobile {
    from { opacity: 0; transform: translateY(100%); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header-custom {
    background: linear-gradient(135deg, var(--success), #059669);
    color: white;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header-custom h5 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-close-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.25rem;
    transition: background 0.2s;
}

.modal-close-btn:hover {
    background: rgba(255,255,255,0.3);
}

.modal-body-custom {
    padding: 1.25rem;
    max-height: calc(90vh - 140px);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

.modal-footer-custom {
    padding: 1rem 1.25rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 0.75rem;
    background: #f8fafc;
}

.modal-footer-custom .btn {
    flex: 1;
    justify-content: center;
}

body.modal-open {
    overflow: hidden;
    position: fixed;
    width: 100%;
    height: 100%;
}

/* Fix para Select2 em mobile */
.select2-container--open {
    z-index: 10000 !important;
}

.select2-dropdown {
    z-index: 10001 !important;
}

/* Previne zoom no iOS */
@media (max-width: 576px) {
    input, select, textarea {
        font-size: 16px !important;
    }
}
    </style>

    <!-- CSS Async -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" media="print" onload="this.media='all'">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
</head>

<body>
    <div class="ambient-glow"></div>

    <!-- TOAST CONTAINER -->
    <div class="toast-container" id="toast-container" role="alert" aria-live="assertive"></div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="d-flex items-center justify-between w-full">
            <a class="navbar-brand" href="painel.php">
                <i class="bi bi-lightning-charge-fill text-warning"></i>
                SGT Propostas
                <?php if (($_SESSION['ambiente'] ?? '') === 'demo'): ?>
                    <span style="background: var(--warning); color: #1e293b; font-size: 0.625rem; padding: 0.125rem 0.5rem; border-radius: 9999px; margin-left: 0.5rem;">DEMO</span>
                <?php endif; ?>
            </a>
            <a href="painel.php" class="btn btn-outline" style="min-height: 36px; padding: 0.5rem 1rem;">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </nav>

    <!-- MAIN FORM -->
    <form action="salvar_proposta.php" method="POST" id="form-proposta" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="form_complete" value="1">

        <div class="wizard-wrapper">

            <!-- PROGRESS -->
            <div class="wizard-progress">
                <div class="steps-container">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <span class="step-label">Cliente</span>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <span class="step-label">Escopo</span>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <span class="step-label">Custos</span>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <span class="step-label">Fechamento</span>
                    </div>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="wizard-content">

                <!-- STEP 1: CLIENTE -->
                <div class="step-panel active" id="step-1">
                    <div class="step-actions">
                        <a href="painel.php" class="btn-action" title="Voltar ao Painel">
                            <i class="bi bi-house-door"></i> Painel
                        </a>
                    </div>
                    <h1 class="section-title">Quem é o Cliente?</h1>
                    <p class="section-subtitle">Selecione o cliente e local da obra.</p>

                    <div class="form-row cols-2">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label" for="id_cliente">Cliente *</label>
                            <div class="cliente-selector-wrapper">
                                <select class="form-select" name="id_cliente" id="id_cliente" required>
                                    <option value="">Buscar cliente...</option>
                                    <?php foreach ($clientes as $c):
                                        $contato = explode(' ', $c['nome_cliente'])[0] . ' - ' . ($c['celular'] ?: $c['telefone'] ?: 'Sem contato');
                                    ?>
                                    <option value="<?= $c['id_cliente'] ?>" data-contato="<?= htmlspecialchars($contato) ?>" <?= (isset($_REQUEST['id_cliente']) && $_REQUEST['id_cliente'] == $c['id_cliente']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nome_cliente']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-success btn-novo-cliente" id="btn-novo-cliente" title="Cadastrar novo cliente">
                                    <i class="bi bi-plus-lg"></i>
                                    <span class="btn-text-desktop">Novo</span>
                                </button>
                            </div>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label" for="contato_obra">Contato na Obra</label>
                            <input type="text" name="contato_obra" id="contato_obra" class="form-control" placeholder="Ex: Sr. João (Vigia)">
                        </div>

                        <!-- Endereço: Layout responsivo -->
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <div class="address-row">
                                <!-- Endereço da Obra -->
                                <div class="address-field address-field-large">
                                    <label class="form-label" for="endereco">Endereço da Obra *</label>
                                    <input type="text" name="endereco" id="endereco" class="form-control" placeholder="Rua, número, complemento..." required autocomplete="off">
                                </div>
                                <!-- Bairro -->
                                <div class="address-field address-field-small">
                                    <label class="form-label" for="bairro">Bairro</label>
                                    <input type="text" name="bairro" id="bairro" class="form-control" autocomplete="off">
                                </div>
                                <!-- Cidade -->
                                <div class="address-field address-field-medium">
                                    <label class="form-label" for="cidade">Cidade *</label>
                                    <input type="text" name="cidade" id="cidade" class="form-control" required autocomplete="off">
                                </div>
                                <!-- Estado -->
                                <div class="address-field address-field-tiny">
                                    <label class="form-label" for="estado">Estado</label>
                                    <select name="estado" id="estado" class="form-select" autocomplete="off">
                                        <?php foreach ($estados as $e): ?>
                                            <option value="<?= $e['sigla'] ?>" <?= $e['sigla'] === 'MG' ? 'selected' : '' ?>><?= $e['sigla'] ?> - <?= $e['nome'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: ESCOPO -->
                <div class="step-panel" id="step-2">
                    <div class="step-actions">
                        <a href="painel.php" class="btn-action" title="Voltar ao Painel">
                            <i class="bi bi-house-door"></i> Painel
                        </a>
                    </div>
                    <h1 class="section-title">O que será feito?</h1>
                    <p class="section-subtitle">Defina o serviço e prazos.</p>

                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label" for="id_servico">Tipo de Serviço *</label>
                            <select class="form-select" name="id_servico" id="id_servico" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($servicos as $s): ?>
                                    <option value="<?= $s['id'] ?>" data-descricao="<?= htmlspecialchars($s['descricao'] ?? '') ?>" <?= (isset($_REQUEST['id_servico']) && $_REQUEST['id_servico'] == $s['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="tipo_levantamento">Título na Proposta</label>
                            <input type="text" name="tipo_levantamento" id="tipo_levantamento" class="form-control" placeholder="Ex: Levantamento Planialtimétrico">
                        </div>

                        <!-- Linha Customizada: Finalidade (9) + Área (3) -->
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <div class="row-custom-9-3" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                <div style="flex: 9; min-width: 300px;">
                                    <label class="form-label">Descrição / Finalidade</label>
                                    <textarea class="form-control" name="finalidade" id="finalidade" rows="3" placeholder="Descreva o objetivo do trabalho..."></textarea>
                                </div>
                                <div style="flex: 3; min-width: 150px;">
                                    <label class="form-label" for="area">Área</label>
                                    <div class="d-flex gap-2">
                                        <input type="number" name="area" id="area" class="form-control" placeholder="0.00" step="0.01" inputmode="decimal">
                                        <select name="unidade_area" id="unidade_area" class="form-select" style="max-width: 100px;" aria-label="Unidade de medida">
                                            <option value="m²" selected>m²</option>
                                            <option value="ha">ha</option>
                                            <option value="km²">km²</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Linha de Prazos (Agrupada) -->
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label mb-2">Cronograma (Dias Úteis)</label>
                            <!-- Flexbox manual para garantir linha única -->
                            <div style="display: flex; gap: 10px; width: 100%;">
                                <div style="flex: 1;">
                                    <label class="text-xs text-slate-500 font-bold uppercase" style="font-size: 10px;">Campo</label>
                                    <input type="number" name="dias_campo" id="dias_campo" class="form-control recalc" value="1" min="0" inputmode="numeric">
                                </div>
                                <div style="flex: 1;">
                                    <label class="text-xs text-slate-500 font-bold uppercase" style="font-size: 10px;">Escritório</label>
                                    <input type="number" name="dias_escritorio" id="dias_escritorio" class="form-control recalc" value="4" min="0" inputmode="numeric">
                                </div>
                                <div style="flex: 1.5;">
                                    <label class="text-xs text-slate-500 font-bold uppercase" style="font-size: 10px;">Prazo Final</label>
                                    <input type="text" name="prazo_execucao" id="prazo_execucao" class="form-control bg-slate-100 font-bold text-slate-700" readonly style="font-size: 12px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: CUSTOS -->
                <div class="step-panel" id="step-3">
                    <div class="step-actions">
                        <a href="painel.php" class="btn-action" title="Voltar ao Painel">
                            <i class="bi bi-house-door"></i> Painel
                        </a>
                        <button type="button" class="btn-action btn-action-calc" onclick="openCalculator()" title="Abrir Calculadora">
                            <i class="bi bi-calculator"></i> Calculadora
                        </button>
                    </div>
                    <h1 class="section-title">Custos Operacionais</h1>
                    <p class="section-subtitle">Adicione os recursos necessários.</p>

                    <!-- Tabs -->
                    <div class="cost-tabs" id="cost-tabs">
                        <button type="button" class="cost-tab active" data-tab="equipe">
                            <i class="bi bi-people"></i> Equipe
                        </button>
                        <button type="button" class="cost-tab" data-tab="estadia">
                            <i class="bi bi-house"></i> Estadia
                        </button>
                        <button type="button" class="cost-tab" data-tab="consumo">
                            <i class="bi bi-fuel-pump"></i> Combustível
                        </button>
                        <button type="button" class="cost-tab" data-tab="equipamentos">
                            <i class="bi bi-tools"></i> Equipamentos
                        </button>
                        <button type="button" class="cost-tab" data-tab="admin">
                            <i class="bi bi-briefcase"></i> Admin
                        </button>
                    </div>

                    <!-- Panels -->
                    <div class="cost-panels">
                        <div class="cost-panel active" id="panel-equipe">
                            <div id="list-salarios" class="cost-list"></div>
                            <button type="button" class="btn btn-add" id="add-salario">
                                <i class="bi bi-plus-lg"></i> Adicionar Profissional
                            </button>
                        </div>

                        <div class="cost-panel hidden" id="panel-estadia">
                            <div id="list-estadia" class="cost-list"></div>
                            <button type="button" class="btn btn-add" id="add-estadia">
                                <i class="bi bi-plus-lg"></i> Adicionar Estadia
                            </button>
                        </div>

                        <div class="cost-panel hidden" id="panel-consumo">
                            <div id="list-consumos" class="cost-list"></div>
                            <button type="button" class="btn btn-add" id="add-consumo">
                                <i class="bi bi-plus-lg"></i> Adicionar Combustível
                            </button>
                        </div>

                        <div class="cost-panel hidden" id="panel-equipamentos">
                            <div id="list-locacao" class="cost-list"></div>
                            <button type="button" class="btn btn-add" id="add-locacao">
                                <i class="bi bi-plus-lg"></i> Adicionar Equipamento
                            </button>
                        </div>

                        <div class="cost-panel hidden" id="panel-admin">
                            <div id="list-admin" class="cost-list"></div>
                            <button type="button" class="btn btn-add" id="add-admin">
                                <i class="bi bi-plus-lg"></i> Adicionar Custo Admin
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: FECHAMENTO -->
                <!-- STEP 4: FECHAMENTO (V2 Dashboard Style - Matching Design) -->
                <div class="step-panel" id="step-4">
                    <div class="step-actions">
                        <a href="painel.php" class="btn-action" title="Voltar ao Painel">
                            <i class="bi bi-house-door"></i> Painel
                        </a>
                        <button type="button" class="btn-action btn-action-calc" onclick="openCalculator()" title="Abrir Calculadora">
                            <i class="bi bi-calculator"></i> Calculadora
                        </button>
                    </div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; line-height: 1.2;">Fechamento da Proposta</h1>
                    <p style="color: #64748b; font-size: 0.875rem; margin-bottom: 1.5rem;">Revise os valores e defina as condições comerciais.</p>

                    <div class="closing-section">

                        <!-- Top Section: Cost Summary vs Profit (45% / 55%) -->
                        <div class="step-4-grid-top">
                            <!-- Left: Cost Summary Box (45%) -->
                            <div class="cost-summary-box">
                                <div class="cost-summary-title">Resumo de Custos</div>
                                <!-- Cost Details -->
                                <div class="cost-summary-details">
                                    <div class="cost-summary-row">
                                        <span>Equipe:</span>
                                        <span id="resumo-salarios-display">R$ 0,00</span>
                                    </div>
                                    <div class="cost-summary-row">
                                        <span>Estadia:</span>
                                        <span id="resumo-estadia-display">R$ 0,00</span>
                                    </div>
                                    <div class="cost-summary-row">
                                        <span>Combustível:</span>
                                        <span id="resumo-consumos-display">R$ 0,00</span>
                                    </div>
                                    <div class="cost-summary-row">
                                        <span>Equipamentos:</span>
                                        <span id="resumo-locacao-display">R$ 0,00</span>
                                    </div>
                                </div>
                                <!-- Hidden fields for calculation (always hidden) -->
                                <div style="display: none !important; visibility: hidden; position: absolute; left: -9999px;">
                                    <strong id="resumo-salarios">0</strong>
                                    <strong id="resumo-estadia">0</strong>
                                    <strong id="resumo-consumos">0</strong>
                                    <strong id="resumo-locacao">0</strong>
                                    <strong id="resumo-admin">0</strong>
                                </div>
                                <div class="cost-summary-total-row">
                                    <span>Total Custos:</span>
                                    <span class="text-red-bold" id="total-custos-geral">R$ 0,00</span>
                                </div>
                            </div>

                            <!-- Right: Margin & Discount (55%) -->
                            <div class="margin-section">
                                <!-- Margem de Lucro -->
                                <div>
                                    <label class="label-padrao">Margem de Lucro (%)</label>
                                    <div class="margin-input-wrapper">
                                        <input type="number" name="percentual_lucro" id="percentual_lucro" class="form-control" value="30" step="0.1" min="0" inputmode="decimal">
                                        <span class="percent-label">%</span>
                                    </div>
                                    <div class="margin-value-display" id="valor-lucro">+ R$ 0,00</div>
                                </div>

                                <!-- Desconto -->
                                <div>
                                    <label class="label-padrao">Desconto (R$)</label>
                                    <input type="number" name="valor_desconto" id="valor_desconto" class="form-control" value="0" step="0.01" min="0" inputmode="decimal">
                                </div>
                            </div>
                        </div>

                        <div class="divider-horizontal"></div>

                        <!-- Bottom Section: Payment vs Final Value (55% / 45%) -->
                        <div class="step-4-grid-bottom">
                            <!-- Left: Condições de Pagamento (55%) -->
                            <div>
                                <div class="payment-conditions-title">Condições de Pagamento</div>
                                <div class="payment-grid">
                                    <!-- Row 1: Entrada -->
                                    <div class="payment-input-group">
                                        <label class="label-padrao">Entrada %</label>
                                        <input type="number" name="mobilizacao_percentual" id="mobilizacao_percentual" class="form-control" value="30" min="0" max="100" inputmode="numeric">
                                    </div>
                                    <div class="payment-input-group">
                                        <label class="label-padrao">Valor Entrada</label>
                                        <input type="text" id="mobilizacao_valor_display" class="form-control input-readonly" readonly value="R$ 0,00">
                                    </div>
                                    <!-- Row 2: Restante -->
                                    <div class="payment-input-group">
                                        <label class="label-padrao">Restante %</label>
                                        <div class="restante-input-group">
                                            <input type="text" id="restante_percentual_display" class="form-control input-readonly" value="70" readonly>
                                            <span class="percent-text">%</span>
                                        </div>
                                    </div>
                                    <div class="payment-input-group">
                                        <label class="label-padrao">Valor Restante</label>
                                        <input type="text" id="restante_valor_display" class="form-control input-readonly" readonly value="R$ 0,00">
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Final Value Display (45%) -->
                            <div class="final-value-container">
                                <span class="big-total-label">VALOR FINAL</span>
                                <span class="big-total-value" id="valor-final-proposta">R$ 0,00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="wizard-footer">
                <button type="button" class="btn btn-outline hidden" id="btn-prev">
                    <i class="bi bi-arrow-left"></i> <span class="hidden sm:inline">Voltar</span>
                </button>
                <div class="flex-1"></div>
                <button type="button" class="btn btn-primary" id="btn-next">
                    Próximo <i class="bi bi-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-primary hidden" id="btn-legacy" name="acao" value="gerar_doc" style="margin-right: 10px;">
                    <i class="bi bi-file-earmark-word"></i> Gerar Proposta
                </button>
                <button type="button" class="btn btn-success hidden" id="btn-finish" onclick="irParaEditor()">
                    <i class="bi bi-magic"></i> Editor Avançado ✨
                </button>
            </div>
        </div>

        <!-- HIDDENS -->
        <input type="hidden" name="total_custos_salarios" id="hidden_total_custos_salarios">
        <input type="hidden" name="total_custos_estadia" id="hidden_total_custos_estadia">
        <input type="hidden" name="total_custos_consumos" id="hidden_total_custos_consumos">
        <input type="hidden" name="total_custos_locacao" id="hidden_total_custos_locacao">
        <input type="hidden" name="total_custos_admin" id="hidden_total_custos_admin">
        <input type="hidden" name="valor_lucro" id="hidden_valor_lucro">
        <input type="hidden" name="subtotal_com_lucro" id="hidden_subtotal_com_lucro">
        <input type="hidden" name="valor_final_proposta" id="hidden_valor_final_proposta">
        <input type="hidden" name="mobilizacao_valor" id="hidden_mobilizacao_valor">
        <input type="hidden" name="restante_percentual" id="hidden_restante_percentual">
        <input type="hidden" name="restante_valor" id="hidden_restante_valor">
    </form>

    <!-- CALCULATOR MODAL -->
    <div class="calculator-modal" id="calculatorModal">
        <div class="calculator-box">
            <div class="calculator-header">
                <h5><i class="bi bi-calculator"></i> Calculadora</h5>
                <button type="button" class="calculator-close" onclick="closeCalculator()">&times;</button>
            </div>
            <div class="calculator-display" id="calcDisplay">0</div>
            <div class="calculator-buttons">
                <button type="button" class="calc-btn calc-btn-clear" onclick="calcClear()">C</button>
                <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('(')">(</button>
                <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput(')')">)</button>
                <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('/')">÷</button>

                <button type="button" class="calc-btn" onclick="calcInput('7')">7</button>
                <button type="button" class="calc-btn" onclick="calcInput('8')">8</button>
                <button type="button" class="calc-btn" onclick="calcInput('9')">9</button>
                <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('*')">×</button>

                <button type="button" class="calc-btn" onclick="calcInput('4')">4</button>
                <button type="button" class="calc-btn" onclick="calcInput('5')">5</button>
                <button type="button" class="calc-btn" onclick="calcInput('6')">6</button>
                <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('-')">−</button>

                <button type="button" class="calc-btn" onclick="calcInput('1')">1</button>
                <button type="button" class="calc-btn" onclick="calcInput('2')">2</button>
                <button type="button" class="calc-btn" onclick="calcInput('3')">3</button>
                <button type="button" class="calc-btn calc-btn-operator" onclick="calcInput('+')">+</button>

                <button type="button" class="calc-btn" onclick="calcInput('0')">0</button>
                <button type="button" class="calc-btn" onclick="calcInput('.')">.</button>
                <button type="button" class="calc-btn" onclick="calcBackspace()">⌫</button>
                <button type="button" class="calc-btn calc-btn-equals" onclick="calcEquals()">=</button>
            </div>
        </div>
    </div>

    <script>
        // Calculator Functions
        let calcExpression = '';

        function openCalculator() {
            document.getElementById('calculatorModal').classList.add('show');
            calcExpression = '';
            document.getElementById('calcDisplay').textContent = '0';
        }

        function closeCalculator() {
            document.getElementById('calculatorModal').classList.remove('show');
        }

        function calcInput(val) {
            if (calcExpression === '0' && val !== '.') {
                calcExpression = val;
            } else {
                calcExpression += val;
            }
            document.getElementById('calcDisplay').textContent = calcExpression || '0';
        }

        function calcClear() {
            calcExpression = '';
            document.getElementById('calcDisplay').textContent = '0';
        }

        function calcBackspace() {
            calcExpression = calcExpression.slice(0, -1);
            document.getElementById('calcDisplay').textContent = calcExpression || '0';
        }

        function calcEquals() {
            try {
                const result = eval(calcExpression);
                const formatted = Number.isInteger(result) ? result : parseFloat(result.toFixed(4));
                document.getElementById('calcDisplay').textContent = formatted;
                calcExpression = String(formatted);
            } catch (e) {
                document.getElementById('calcDisplay').textContent = 'Erro';
                calcExpression = '';
            }
        }

        // Close calculator on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('calculatorModal').classList.contains('show')) {
                closeCalculator();
            }
        });

        // Close calculator when clicking outside
        document.getElementById('calculatorModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCalculator();
            }
        });
    </script>

    <!-- MODAL NOVO CLIENTE -->
    <div class="modal" id="modalNovoCliente">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 style="margin: 0; font-size: 1.125rem;"><i class="bi bi-person-plus-fill"></i> Novo Cliente</h5>
                <button type="button" class="btn btn-close-modal" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-novo-cliente">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="acao" value="criar_ajax">

                    <div class="form-row cols-2">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Nome *</label>
                            <input type="text" name="nome_cliente" class="form-control" required placeholder="Nome completo">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Empresa</label>
                            <input type="text" name="empresa" class="form-control" placeholder="Nome da empresa">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CPF/CNPJ</label>
                            <input type="text" name="cnpj_cpf" class="form-control" placeholder="000.000.000-00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control" placeholder="email@exemplo.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Celular *</label>
                            <input type="tel" name="celular" class="form-control" placeholder="(31) 99999-9999" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Telefone</label>
                            <input type="tel" name="telefone" class="form-control" placeholder="(31) 3333-3333">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close-modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-salvar-cliente">
                    <i class="bi bi-check-lg"></i> Salvar
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Dados PHP para JS (JSON seguro)
        window.SGT_DATA = {
            opcoesFuncao: <?= json_encode($tipos_funcao, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesEstadia: <?= json_encode($tipos_estadia, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesConsumo: <?= json_encode($tipos_consumo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesLocacao: <?= json_encode($tipos_locacao, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            opcoesAdmin: <?= json_encode($tipos_admin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            marcasPorTipo: <?= json_encode($marcas_por_tipo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            enderecoEmpresa: <?= json_encode($empresa_endereco, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        };
    </script>
    </form>
    <script src="calculos.js?v=<?= time() ?>"></script>

    <script>
        // Wizard Navigation
        const Wizard = {
            current: 1,
            total: 4,

            init() {
                this.bindEvents();
                this.updateUI();
                this.initSelect2();
                this.initTabs();

                // Trigger change if service is pre-selected from PHP (Wizard)
                if ($('#id_servico').val()) {
                    $('#id_servico').trigger('change');
                }
            },

            bindEvents() {
                $('#btn-next').on('click', () => this.next());
                $('#btn-prev').on('click', () => this.prev());

                // Swipe support
                let touchStartX = 0;
                $('.wizard-content').on('touchstart', e => {
                    touchStartX = e.changedTouches[0].screenX;
                }).on('touchend', e => {
                    const diff = touchStartX - e.changedTouches[0].screenX;
                    if (Math.abs(diff) > 50) {
                        diff > 0 ? this.next() : this.prev();
                    }
                });

                // Auto-fill cliente
                $('#id_cliente').on('select2:select', function() {
                    $('#contato_obra').val($(this).find(':selected').data('contato'));
                });

                // Auto-fill serviço
                $('#id_servico').on('change', function() {
                    const $opt = $(this).find(':selected');
                    const desc = $opt.data('descricao');
                    if (desc) $('#finalidade').val(desc);
                    $('#tipo_levantamento').val('Levantamento ' + $opt.text());
                });
            },

            initSelect2() {
                // Mobile detection
                const isMobile = window.matchMedia('(pointer: coarse)').matches;

                // DESTROI se já existir (evita duplicatas e limpa configs antigas)
                if ($('#id_cliente').hasClass('select2-hidden-accessible')) {
                    $('#id_cliente').select2('destroy');
                }

                $('#id_cliente').select2({
                    placeholder: 'Buscar cliente...',
                    allowClear: true,
                    theme: 'default',
                    dropdownParent: $('body'), // Mantemos no Body pois é melhor para mobile/z-index
                    minimumResultsForSearch: 5,
                    selectOnClose: !isMobile
                });
            },

            initTabs() {
                $('.cost-tab').on('click', function() {
                    const tab = $(this).data('tab');
                    $('.cost-tab').removeClass('active');
                    $(this).addClass('active');
                    $('.cost-panel').addClass('hidden');
                    $(`#panel-${tab}`).removeClass('hidden');
                });
            },

            validate() {
                if (this.current === 1) {
                    if (!$('#id_cliente').val()) {
                        showToast('Selecione um cliente', 'error');
                        $('#id_cliente').select2('open');
                        return false;
                    }
                    if (!$('input[name="endereco"]').val()) {
                        showToast('Informe o endereço da obra', 'error');
                        $('input[name="endereco"]').focus();
                        return false;
                    }
                }
                if (this.current === 2 && !$('#id_servico').val()) {
                    showToast('Selecione o tipo de serviço', 'error');
                    $('#id_servico').focus();
                    return false;
                }
                return true;
            },

            next() {
                if (this.current < this.total && this.validate()) {
                    this.current++;
                    this.updateUI();
                }
            },

            prev() {
                if (this.current > 1) {
                    this.current--;
                    this.updateUI();
                }
            },

            updateUI() {
                // Panels
                $('.step-panel').removeClass('active');
                $(`#step-${this.current}`).addClass('active');

                // Steps indicator
                $('.step').removeClass('active completed');
                for (let i = 1; i < this.current; i++) {
                    $(`.step[data-step="${i}"]`).addClass('completed');
                }
                $(`.step[data-step="${this.current}"]`).addClass('active');

                // Buttons
                $('#btn-prev').toggleClass('hidden', this.current === 1);
                $('#btn-next').toggleClass('hidden', this.current === this.total);
                $('#btn-legacy').toggleClass('hidden', this.current !== this.total);
                $('#btn-finish').toggleClass('hidden', this.current !== this.total);

                // Scroll to top
                $('.wizard-content').scrollTop(0);
                
                // Acessibilidade: Move foco para o título da seção para leitores de tela
                const $currentPanel = $(`#step-${this.current}`);
                const $title = $currentPanel.find('.section-title');
                
                if ($title.length) {
                    $title.attr('tabindex', '-1').focus();
                } else {
                    // Fallback para o primeiro input se não achar título
                    $currentPanel.find('input, select').first().focus();
                }
            }
        };

        // Modal Cliente
        const ModalCliente = {
            init() {
                const modal = $('#modalNovoCliente');

                $('#btn-novo-cliente').on('click', () => {
                    $('#form-novo-cliente')[0].reset();
                    modal.addClass('show');
                });

                $('.btn-close-modal').on('click', () => modal.removeClass('show'));

                $('#btn-salvar-cliente').on('click', () => this.salvar());

                // Close on backdrop click
                modal.on('click', e => {
                    if (e.target === modal[0]) modal.removeClass('show');
                });
            },

            salvar() {
                const $btn = $('#btn-salvar-cliente');
                const nome = $('#modalNovoCliente input[name="nome_cliente"]').val().trim();

                if (!nome) {
                    showToast('Nome é obrigatório', 'error');
                    return;
                }

                $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Salvando...');

                $.ajax({
                    url: 'salvar_cliente_ajax.php',
                    method: 'POST',
                    data: $('#form-novo-cliente').serialize(),
                    dataType: 'json',
                    success: (res) => {
                        if (res.success) {
                            const contato = res.nome.split(' ')[0] + ' - ' + (res.celular || '-');
                            const $option = new Option(res.nome, res.id, true, true);
                            $('#id_cliente').append($option).trigger('change');
                            $('#contato_obra').val(contato);

                            $('#modalNovoCliente').removeClass('show');
                            showToast('Cliente cadastrado!', 'success');
                        } else {
                            showToast(res.error || 'Erro ao salvar', 'error');
                        }
                    },
                    error: () => showToast('Erro de conexão', 'error'),
                    complete: () => {
                        $btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Salvar');
                    }
                });
            }
        };

        // Toast Notification
        function showToast(message, type = 'info') {
            const icons = {
                success: 'check-circle-fill',
                error: 'exclamation-triangle-fill',
                warning: 'exclamation-circle-fill',
                info: 'info-circle-fill'
            };

            const $toast = $(`
                <div class="toast ${type}">
                    <i class="bi bi-${icons[type] || icons.info} text-${type === 'error' ? 'danger' : type}"></i>
                    <span>${message}</span>
                </div>
            `);

            $('#toast-container').append($toast);
            setTimeout(() => $toast.fadeOut(() => $toast.remove()), 4000);
        }

        // Init
        $(() => {
            Wizard.init();
            ModalCliente.init();
        });

        function irParaEditor() {
            const form = document.getElementById('form-proposta');
            // Muda o destino para o Editor
            // Obs: O Editor vai receber os POSTs e preencher a tela
            form.action = 'editor_dinamico.php';
            form.submit();
        }
    </script>
    <script>
        // Auto-Save Logic (LocalStorage)
        const AutoSave = {
            key: 'sgt_proposta_draft',
            init() {
                // Check if this is a new proposal request - clear old data
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('nova') || urlParams.get('nova') === '1') {
                    this.clear();
                    // Remove ?nova parameter from URL without reload
                    const cleanUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                    return; // Don't restore old data
                }

                this.restore();
                this.bindEvents();
            },
            bindEvents() {
                $('#form-proposta').on('change input', 'input, select, textarea', () => {
                    this.save();
                });

                // Limpar rascunho ao enviar com sucesso
                $('#form-proposta').on('submit', () => {
                    this.clear();
                });
            },
            save() {
                const data = $('#form-proposta').serializeArray();
                localStorage.setItem(this.key, JSON.stringify(data));
                $('#status-autosave').html('<i class="bi bi-cloud-check"></i> Salvo').fadeIn().delay(1000).fadeOut();
            },
            clear() {
                localStorage.removeItem(this.key);
                console.log('Rascunho limpo para nova proposta');
            },
            restore() {
                const draft = localStorage.getItem(this.key);
                if (!draft) return;

                try {
                    const data = JSON.parse(draft);
                    data.forEach(field => {
                        const $el = $(`[name="${field.name}"]`);
                        if ($el.length) {
                            if ($el.is(':radio, :checkbox')) {
                                $el.filter(`[value="${field.value}"]`).prop('checked', true);
                            } else {
                                $el.val(field.value).trigger('change'); // Trigger change for Select2/Calculations
                            }
                        }
                    });
                    showToast('Rascunho recuperado!', 'info');
                } catch (e) {
                    console.error('Erro ao restaurar rascunho', e);
                }
            }
        };

        // Monitoramento de Conexão
        window.addEventListener('online', () => showToast('Conexão restabelecida! 🟢', 'success'));
        window.addEventListener('offline', () => showToast('Você está offline. 🔴', 'warning'));

        $(() => {
            // Init Autosave after other scripts
            setTimeout(() => AutoSave.init(), 500);
        });
    </script>
</body>

</html>