<?php
// Arquivo: admin_guard.php
require_once 'core.php';

/*
  Apenas admin pode usar
*/
exigeAdmin();

/*
  Admin pode navegar invisível
  Não cria dados
  Não paga
  Não assina
*/
define('ADMIN_INVISIVEL', true);
