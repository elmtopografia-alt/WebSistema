
# Script de Backup Automático para GitHub
# Antigravity Agent

$projectPath = "f:\Site\Sistema Proposta\SistemaWeb"
$logFile = "$projectPath\backup_log.txt"
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

function Log-Message {
    param([string]$message)
    $logMsg = "[$timestamp] $message"
    Add-Content -Path $logFile -Value $logMsg
    Write-Host $logMsg
}

try {
    # 1. Navegar para a pasta
    if (-not (Test-Path $projectPath)) {
        throw "Diretório não encontrado: $projectPath"
    }
    Set-Location $projectPath

    # 2. Verificar status do git
    Log-Message "Iniciando backup..."
    
    # 3. Adicionar arquivos
    git add . 2>&1 | Out-String | ForEach-Object { Log-Message "GIT ADD: $_" }

    # 4. Verificar se há algo para commitar
    $status = git status --porcelain
    if ([string]::IsNullOrWhiteSpace($status)) {
        Log-Message "Nenhuma alteração detectada. Backup pulado."
        exit
    }

    # 5. Commit
    git commit -m "Backup Diário Automático: $timestamp" 2>&1 | Out-String | ForEach-Object { Log-Message "GIT COMMIT: $_" }

    # 6. Push
    git push 2>&1 | Out-String | ForEach-Object { Log-Message "GIT PUSH: $_" }

    Log-Message "Backup concluído com sucesso."
}
catch {
    Log-Message "ERRO CRÍTICO: $($_.Exception.Message)"
}
