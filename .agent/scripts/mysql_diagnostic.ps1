# MySQL Deep Diagnostic
$port = 3306
$processName = "mysqld"

Write-Host "--- MySQL Deep Diagnostic ---" -ForegroundColor Cyan

# 1. Check if Process is Running
$proc = Get-Process -Name $processName -ErrorAction SilentlyContinue
if ($proc) {
    Write-Host "✅ MySQL Process (mysqld.exe) is RUNNING. PID: $($proc.Id)" -ForegroundColor Green
}
else {
    Write-Host "❌ MySQL Process is NOT running." -ForegroundColor Red
}

# 2. Check Port 3306 Ownership
Write-Host "Checking Port $port..."
$netstat = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
if ($netstat) {
    Write-Host "⚠️ Port $port is IN USE by PID: $($netstat.OwningProcess)" -ForegroundColor Yellow
    $owner = Get-Process -Id $netstat.OwningProcess -ErrorAction SilentlyContinue
    if ($owner) {
        Write-Host "   Owner: $($owner.ProcessName)" -ForegroundColor Yellow
    }
}
else {
    Write-Host "ℹ️ Port $port is free (not listening)." -ForegroundColor Gray
}

# 3. Attempt Force Start if Down
if (-not $proc) {
    Write-Host "Attempting FORCE START via XAMPP path..."
    $mysqlPath = "C:\xampp\mysql\bin\mysqld.exe"
    if (Test-Path $mysqlPath) {
        Start-Process -FilePath $mysqlPath -WindowStyle Hidden
        Start-Sleep -Seconds 5
        $check = Get-Process -Name $processName -ErrorAction SilentlyContinue
        if ($check) {
            Write-Host "✅ MySQL Successfully Started!" -ForegroundColor Green
        }
        else {
            Write-Host "❌ Failed to start MySQL. Check logs at C:\xampp\mysql\data\mysql_error.log" -ForegroundColor Red
        }
    }
    else {
        Write-Host "X MySQL Executable not found at $mysqlPath" -ForegroundColor Red
    }
}
