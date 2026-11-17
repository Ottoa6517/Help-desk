<#
.SYNOPSIS
  Respalda la base de datos de osTicket y su archivo de configuración. Conserva los últimos N respaldos.
.DESCRIPTION
  Un help desk sin respaldo pierde todo el historial si se daña el disco. Este script genera:
    respaldos\osticket-AAAAMMDD-HHMMSS.sql.zip  (volcado completo con mysqldump)
  La carpeta respaldos\ está en .gitignore: nunca se sube a GitHub.
  Para restaurar:  mysql -u root -p osticket_db < archivo.sql
.EXAMPLE
  powershell -ExecutionPolicy Bypass -File .\scripts\windows\04-respaldo.ps1 -Conservar 7
#>
param([string]$Xampp = 'C:\xampp', [int]$Conservar = 7, [string]$Carpeta = "$PSScriptRoot\..\..\respaldos")
$ErrorActionPreference = 'Stop'
$dump = "$Xampp\mysql\bin\mysqldump.exe"
New-Item $Carpeta -ItemType Directory -Force | Out-Null
$Carpeta = (Resolve-Path $Carpeta).Path
$nombre = 'osticket-' + (Get-Date -Format 'yyyyMMdd-HHmmss')
$sql = Join-Path $Carpeta "$nombre.sql"

$s = Read-Host 'Contraseña de osticket_user' -AsSecureString
$b = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s)
$tmp = New-TemporaryFile
try {
    $clave = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($b)
    [IO.File]::WriteAllText($tmp, "[client]`r`nuser=osticket_user`r`npassword=`"$clave`"`r`nhost=localhost`r`n")
    & $dump "--defaults-extra-file=$tmp" --single-transaction --routines --default-character-set=utf8mb4 --result-file="$sql" osticket_db
    if ($LASTEXITCODE) { throw 'mysqldump falló.' }
} finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($b); Remove-Item $tmp -Force
}
Compress-Archive -Path $sql -DestinationPath "$sql.zip"; Remove-Item $sql
$tam = [math]::Round((Get-Item "$sql.zip").Length / 1KB)
Write-Host "Respaldo creado: $sql.zip ($tam KB)" -ForegroundColor Green

Get-ChildItem $Carpeta -Filter 'osticket-*.sql.zip' | Sort-Object LastWriteTime -Descending |
    Select-Object -Skip $Conservar | ForEach-Object { Remove-Item $_.FullName; Write-Host "Borrado respaldo viejo: $($_.Name)" }
