# MySQL Deep Diagnostic v2
$port = 3306
$processName = "mysqld"

Write-Host "--- MySQL Deep Diagnostic ---" -ForegroundColor Cyan

# 1. Check Process
$proc = Get-Process -Name $processName -ErrorAction SilentlyContinue
if ($proc) {
    Write-Host "MySQL Process (mysqld.exe) is RUNNING. PID: $($proc.Id)" -ForegroundColor Green
}
else {
    Write-Host "MySQL Process is NOT running." -ForegroundColor Red
}

# 2. Check Port
Write-Host "Checking Port $port..."
$netstat = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
if ($netstat) {
    Write-Host "Port $port is IN USE." -ForegroundColor Yellow
}
else {
    Write-Host "Port $port is free (not listening)." -ForegroundColor Gray
}

# 3. Attempt Force Start
if (-not $proc) {
    Write-Host "Attempting FORCE START via XAMPP path..."
    $mysqlPath = "C:\xampp\mysql\bin\mysqld.exe"
    if (Test-Path $mysqlPath) {
        $startArgs = @{
            FilePath    = $mysqlPath
            WindowStyle = "Hidden"
        }
        Start-Process @startArgs
        Write-Host "Start command sent. Waiting 5s..."
        Start-Sleep -Seconds 5
        
        $check = Get-Process -Name $processName -ErrorAction SilentlyContinue
        if ($check) {
            Write-Host "MySQL Successfully Started!" -ForegroundColor Green
        }
        else {
            Write-Host "Failed to start. Check error logs." -ForegroundColor Red
        }
    }
    else {
        Write-Host "MySQL Executable not found at default XAMPP path." -ForegroundColor Red
    }
}
