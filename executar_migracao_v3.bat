@echo off
chcp 65001 >nul
title Migração v3.0 - Conversão + Validação

echo ==========================================
echo  MIGRACAO v3.0 - Modelos DOCX Unificados
echo ==========================================
echo.

cd /d "F:\Site\Sistema Proposta\SistemaWeb\SistemaWeb"

:: Verifica se PHP está disponível
php -v >nul 2>&1
if errorlevel 1 (
    echo [ERRO] PHP nao encontrado no PATH
    pause
    exit /b 1
)

echo [1/2] Executando conversor...
php scripts\conversor_docx_v3.php
if errorlevel 1 (
    echo [ERRO] Falha na conversao
    pause
    exit /b 1
)

echo.
echo [2/2] Executando validador...
php scripts\validador_v3.php modelos_unificados
if errorlevel 1 (
    echo [ERRO] Falha na validacao
    pause
    exit /b 1
)

echo.
echo ==========================================
echo  MIGRACAO CONCLUIDA!
echo ==========================================
echo.
echo Proximos passos:
echo 1. Revise o relatorio JSON gerado
echo 2. Corrija chaves invalidas manualmente nos DOCX
echo 3. Execute o validador novamente
echo.
pause