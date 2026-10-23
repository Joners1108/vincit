#Requires -Version 5.1
<#
  Backup completo para o disco D: — bases de dados MySQL/MariaDB + pastas de trabalho (Laragon / XAMPP).
  Executar como utilizador normal (botão direito > Executar com PowerShell), ou:
    powershell -ExecutionPolicy Bypass -File "C:\laragon\www\vincit\backup-para-disco-d.ps1"

  Antes: fecha o MySQL no Laragon/XAMPP se o dump falhar por ficheiros em uso (ou usa só mysqldump).
#>

$ErrorActionPreference = "Continue"
$stamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
$BackupRoot = Join-Path "D:\Backup" "VincitBackup_$stamp"

# Palavra-passe MySQL (Laragon/XAMPP local costuma ser vazia — altera se tiveres root com password)
$mysqlUser = "root"
$mysqlPass = ""   # exemplo: $mysqlPass = "secret"

function Find-Mysqldump {
    $candidates = @(
        "C:\laragon\bin\mysql\mysql-8.4.3-1-winx64\bin\mysqldump.exe",
        "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe",
        "C:\laragon\bin\mysql\mysql-5.7.24-winx64\bin\mysqldump.exe"
    )
    foreach ($p in $candidates) {
        if (Test-Path $p) { return $p }
    }
    $found = Get-ChildItem -Path "C:\laragon\bin\mysql" -Recurse -Filter "mysqldump.exe" -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($found) { return $found.FullName }
    $xampp = "C:\xampp\mysql\bin\mysqldump.exe"
    if (Test-Path $xampp) { return $xampp }
    $found2 = Get-ChildItem -Path "C:\xampp\mysql" -Recurse -Filter "mysqldump.exe" -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($found2) { return $found2.FullName }
    return $null
}

if (-not (Test-Path "D:\")) {
    Write-Host "ERRO: O disco D: nao existe ou nao esta acessivel." -ForegroundColor Red
    exit 1
}

New-Item -ItemType Directory -Force -Path $BackupRoot | Out-Null
$logFile = Join-Path $BackupRoot "backup-log.txt"
function Log($m) { Add-Content -Path $logFile -Value "$(Get-Date -Format o) $m"; Write-Host $m }

Log "Pasta de backup: $BackupRoot"

# --- MySQL / MariaDB (dump SQL) ---
$mysqldump = Find-Mysqldump
if ($mysqldump) {
    Log "mysqldump encontrado: $mysqldump"
    $sqlDir = Join-Path $BackupRoot "mysql-dumps"
    New-Item -ItemType Directory -Force -Path $sqlDir | Out-Null

    $argsUser = @("-u", $mysqlUser)
    if ($mysqlPass) { $argsUser += "-p$mysqlPass" }
    $argsCommon = $argsUser + @("--single-transaction", "--routines", "--events")

    $allSql = Join-Path $sqlDir "TODAS_AS_BASES.sql"
    try {
        $dumpArgs = $argsCommon + @("--all-databases")
        & $mysqldump @dumpArgs 2>&1 | Out-File -FilePath $allSql -Encoding utf8
        if ((Test-Path $allSql) -and ((Get-Item $allSql).Length -gt 0)) {
            Log "OK: dump completo -> $allSql"
        } else {
            Log "AVISO: TODAS_AS_BASES.sql vazio ou falhou - verifica password MySQL e se o servico esta a correr."
        }
    } catch {
        Log "AVISO dump --all-databases: $_"
    }

    # Cópia por base (útil se o dump global falhar)
    try {
        $mysqlExe = Join-Path (Split-Path $mysqldump) "mysql.exe"
        if (Test-Path $mysqlExe) {
            $mysqlArgs = $argsUser + @("-N", "-e", "SHOW DATABASES;")
            $dblist = & $mysqlExe @mysqlArgs 2>$null
            foreach ($db in $dblist) {
                $db = $db.Trim()
                if ($db -match '^(information_schema|performance_schema|mysql|sys)$') { continue }
                if (-not $db) { continue }
                $safe = $db -replace '[^\w\-]', '_'
                $one = Join-Path $sqlDir "$safe.sql"
                $perDb = $argsCommon + @($db)
                & $mysqldump @perDb 2>&1 | Out-File -FilePath $one -Encoding utf8
                if ((Test-Path $one) -and ((Get-Item $one).Length -gt 0)) { Log "  -> $safe.sql" }
            }
        }
    } catch {
        Log "AVISO: bases individuais: $_"
    }
} else {
    Log "AVISO: mysqldump.exe nao encontrado. Instala MySQL no Laragon/XAMPP ou adiciona o caminho em Find-Mysqldump."
}

# --- Pastas de trabalho (Laragon) ---
$laragonWww = "C:\laragon\www"
if (Test-Path $laragonWww) {
    $dest = Join-Path $BackupRoot "laragon-www"
    Log "A copiar $laragonWww -> $dest (pode demorar)..."
    robocopy $laragonWww $dest /E /COPY:DAT /R:2 /W:5 /XD node_modules .git /NFL /NDL /NJH /NJS
    Log "Robocopy laragon-www terminado (codigos 0-7 = OK no robocopy)."
}

# --- XAMPP htdocs ---
$xamppHtdocs = "C:\xampp\htdocs"
if (Test-Path $xamppHtdocs) {
    $dest = Join-Path $BackupRoot "xampp-htdocs"
    Log "A copiar $xamppHtdocs -> $dest..."
    robocopy $xamppHtdocs $dest /E /COPY:DAT /R:2 /W:5 /XD node_modules .git /NFL /NDL /NJH /NJS
}

# --- Opcional: ficheiros de configuracao Laragon (sem dados gigantes) ---
$laragonEtc = "C:\laragon\etc"
if (Test-Path $laragonEtc) {
    $dest = Join-Path $BackupRoot "laragon-etc"
    New-Item -ItemType Directory -Force -Path $dest | Out-Null
    robocopy $laragonEtc $dest /E /COPY:DAT /R:1 /W:3 /NFL /NDL /NJH /NJS | Out-Null
    Log "Copiado laragon-etc (configs)."
}

Log "FIM. Verifica a pasta: $BackupRoot"
Write-Host "`nBackup concluido em: $BackupRoot" -ForegroundColor Green
Write-Host "Le tambem: $logFile"
