<?php
// Arquivo: financeiro_guard.php
require_once 'core.php';

/*
  Financeiro só em produção
*/
bloqueiaDemoParaFinanceiro();
