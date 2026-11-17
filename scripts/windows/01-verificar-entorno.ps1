<#
.SYNOPSIS
  Revisa que la PC esté lista para osTicket 1.18 con XAMPP. Solo lee, no cambia nada.
.EXAMPLE
  powershell -ExecutionPolicy Bypass -File .\scripts\windows\01-verificar-entorno.ps1
#>
param([string]$Xampp = 'C:\xampp')

$ErrorActionPreference = 'Stop'
$fallas = 0
function Bien($m)  { Write-Host "  [OK]   $m" -ForegroundColor Green }
function Mal($m)   { Write-Host "  [FALTA] $m" -ForegroundColor Red; $script:fallas++ }
function Aviso($m) { Write-Host "  [AVISO] $m" -ForegroundColor Yellow }

Write-Host "`n== Puertos ==" -ForegroundColor Cyan
foreach ($p in 80, 3306) {
    $uso = Get-NetTCPConnection -LocalPort $p -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
    if (-not $uso) { Bien "Puerto $p libre"; continue }
    $proc = (Get-Process -Id $uso.OwningProcess -ErrorAction SilentlyContinue).ProcessName
    if ($proc -in 'httpd', 'mysqld', 'mariadbd') { Bien "Puerto $p en uso por $proc (XAMPP ya está corriendo)" }
    else { Mal "Puerto $p ocupado por '$proc' (PID $($uso.OwningProcess)). Ciérralo o cambia el puerto (ver docs/02-instalacion.md)" }
}

Write-Host "`n== XAMPP ==" -ForegroundColor Cyan
$php = Join-Path $Xampp 'php\php.exe'
if (-not (Test-Path $php)) {
    Mal "No encuentro $php. Instala XAMPP 8.4.x (no el 8.5) en $Xampp"
} else {
    $ver = & $php -r 'echo PHP_VERSION;'
    $v = [version]($ver -replace '[^\d\.].*$', '')
    if ($v -ge [version]'8.2' -and $v -lt [version]'8.5') { Bien "PHP $ver (osTicket 1.18 admite 8.2 a 8.4)" }
    elseif ($v -ge [version]'8.5') { Mal "PHP ${ver}: osTicket 1.18.4 solo está probado hasta PHP 8.4. Usa XAMPP 8.4.x" }
    else { Mal "PHP $ver es demasiado viejo: osTicket 1.18 necesita 8.2 o superior" }

    $mods = (& $php -m) | ForEach-Object { $_.Trim().ToLower() }
    foreach ($m in 'mysqli', 'gd', 'intl', 'mbstring', 'iconv', 'ctype', 'xml', 'dom', 'json', 'phar') {
        if ($mods -contains $m) { Bien "Extensión $m" } else { Mal "Extensión $m desactivada (corre 02-preparar-xampp.ps1)" }
    }
    if ($mods -contains 'zend opcache') { Bien 'OPcache (recomendada)' } else { Aviso 'OPcache apagada: recomendada, no obligatoria' }
    if ($mods -notcontains 'apcu') { Aviso 'APCu no viene en XAMPP: es opcional, osTicket funciona sin ella' }
}

Write-Host "`n== osTicket ==" -ForegroundColor Cyan
$ost = Join-Path $Xampp 'htdocs\osticket'
if (Test-Path $ost) {
    if (Test-Path "$ost\setup") { Aviso 'La carpeta setup existe: bórrala después de instalar (paso 1.6)' } else { Bien 'Carpeta setup borrada' }
    $cfg = "$ost\include\ost-config.php"
    if (Test-Path $cfg) {
        if ((Get-Item $cfg).IsReadOnly) { Bien 'ost-config.php en solo lectura' } else { Aviso 'ost-config.php no está en solo lectura (ponlo después de instalar)' }
    } else { Aviso 'Aún no existe include\ost-config.php' }
} else { Aviso "osTicket todavía no está en $ost" }

Write-Host ''
if ($fallas) { Write-Host "Hay $fallas punto(s) por corregir." -ForegroundColor Red; exit 1 }
Write-Host 'Todo listo.' -ForegroundColor Green
