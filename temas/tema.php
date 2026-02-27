<?php
/**
 * SGT Tema CSS v2
 * Gera folha de estilos dinâmica baseada no parâmetro ?cor=
 * Arquivo chamado via <link href="temas/tema.php?cor=verde">
 */

require_once __DIR__ . '/../core/TemaEngine.php';

header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: max-age=86400');

$cor  = filter_input(INPUT_GET, 'cor', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'verde';
$tema = new TemaEngine($cor);
$p    = $tema->getPaleta();
?>

/* ========================================
   SGT PROPOSTAS - TEMA: <?= $p['nome'] ?>

   Gerado em: <?= date('Y-m-d H:i') ?>

   ======================================== */

:root {
    --sgt-primaria:    #<?= $p['primaria'] ?>;
    --sgt-secundaria:  #<?= $p['secundaria'] ?>;
    --sgt-fundo:       #<?= $p['fundo'] ?>;
    --sgt-texto:       #<?= $p['texto'] ?>;
    --sgt-branco:      #ffffff;
    --sgt-cinza-50:    #f9fafb;
    --sgt-cinza-100:   #f3f4f6;
    --sgt-cinza-200:   #e5e7eb;
    --sgt-cinza-700:   #374151;
    --sgt-cinza-900:   #111827;
    --sgt-sombra:      0 4px 6px -1px rgba(0,0,0,0.1);
}

/* ── Container ─────────────────────────────────────────────── */
.sgt-proposta {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    line-height: 1.6;
    color: var(--sgt-cinza-700);
    max-width: 210mm;
    margin: 0 auto;
    padding: 2rem;
    background: var(--sgt-branco);
}

.sgt-conteudo {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ── Títulos ────────────────────────────────────────────────── */
.sgt-titulo {
    color: var(--sgt-texto);
    font-weight: 700;
    margin: 0;
    padding: 0.75rem 0;
    border-bottom: 2px solid var(--sgt-fundo);
}

.sgt-titulo-principal {
    color: var(--sgt-primaria);
    font-size: 1.5rem;
    text-align: center;
    border-bottom: 3px solid var(--sgt-primaria);
    padding-bottom: 1rem;
    margin-bottom: 1rem;
}

h2.sgt-titulo {
    font-size: 1.125rem;
    border-left: 4px solid var(--sgt-secundaria);
    padding-left: 0.75rem;
}

h3.sgt-titulo {
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    color: var(--sgt-secundaria);
}

/* ── Textos ─────────────────────────────────────────────────── */
.sgt-texto { margin: 0; text-align: justify; }

.sgt-texto-destaque {
    background: var(--sgt-fundo);
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 3px solid var(--sgt-secundaria);
    font-weight: 500;
}

.sgt-texto-valor {
    background: var(--sgt-primaria);
    color: var(--sgt-branco);
    padding: 1rem;
    border-radius: 0.5rem;
    font-size: 1.125rem;
    font-weight: 700;
    text-align: center;
}

/* ── Dados (grid label:valor) ───────────────────────────────── */
.sgt-dados {
    background: var(--sgt-fundo);
    padding: 1rem;
    border-radius: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.sgt-linha {
    display: flex;
    gap: 0.5rem;
    align-items: baseline;
}

.sgt-label {
    font-weight: 600;
    color: var(--sgt-texto);
    min-width: 140px;
    flex-shrink: 0;
}

.sgt-valor {
    color: var(--sgt-cinza-900);
    flex: 1;
}

/* ── Listas ─────────────────────────────────────────────────── */
.sgt-lista {
    margin: 0;
    padding-left: 1.5rem;
}

.sgt-lista li {
    margin-bottom: 0.5rem;
    padding-left: 0.5rem;
}

.sgt-lista li::marker { color: var(--sgt-secundaria); }

/* ── Tabelas ────────────────────────────────────────────────── */
.sgt-tabela {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.sgt-tabela th {
    background: var(--sgt-primaria);
    color: var(--sgt-branco);
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}

.sgt-tabela td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--sgt-cinza-200);
}

.sgt-tabela tr:nth-child(even) { background: var(--sgt-cinza-50); }

.sgt-total {
    background: var(--sgt-primaria) !important;
    color: var(--sgt-branco) !important;
    font-weight: 700;
}

/* ── Responsivo ─────────────────────────────────────────────── */
@media (max-width: 768px) {
    .sgt-proposta { padding: 1rem; }
    .sgt-linha { flex-direction: column; gap: 0.25rem; }
    .sgt-label { min-width: auto; }
}

/* ── Impressão ──────────────────────────────────────────────── */
@media print {
    .sgt-proposta { max-width: 100%; padding: 0; }
    .sgt-dados, .sgt-texto-destaque { break-inside: avoid; }
    .sgt-tabela { break-inside: avoid; }
}
