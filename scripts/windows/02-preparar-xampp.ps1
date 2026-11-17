<#
.SYNOPSIS
  Prepara y asegura XAMPP para el laboratorio de osTicket.
.DESCRIPTION
  1. Respalda php.ini, httpd.conf, my.ini y config.inc.php de phpMyAdmin (.bak con fecha).
  2. Activa las extensiones de PHP que pide osTicket y aplica ajustes seguros de php.ini.
  3. Hace que Apache y MariaDB escuchen solo en 127.0.0.1 (nadie de tu red puede entrar).
  4. Pone contraseña al usuario root de MariaDB (XAMPP lo trae vacío) y cambia phpMyAdmin
     a inicio de sesión con formulario.
  5. Crea la base osticket_db y el usuario osticket_user con permisos solo sobre esa base.
  Las contraseñas se piden en pantalla; no se guardan en ningún archivo del repositorio.
  Ejecútalo con Apache y MySQL DETENIDOS en el XAMPP Control Panel, salvo el paso de MariaDB,
  que el propio script arranca y detiene.
.EXAMPLE
  powershell -ExecutionPolicy Bypass -File .\scripts\windows\02-preparar-xampp.ps1 -WhatIf
  powershell -ExecutionPolicy Bypass -File .\scripts\windows\02-preparar-xampp.ps1
#>
[CmdletBinding(SupportsShouldProcess)]
param([string]$Xampp = 'C:\xampp')

$ErrorActionPreference = 'Stop'
$fecha = Get-Date -Format 'yyyyMMdd-HHmmss'
function Paso($m) { Write-Host "`n== $m ==" -ForegroundColor Cyan }
function Respaldar($ruta) {
    if ((Test-Path $ruta) -and $PSCmdlet.ShouldProcess($ruta, 'Respaldar')) {
        Copy-Item $ruta "$ruta.$fecha.bak"; Write-Host "  Respaldo: $ruta.$fecha.bak"
    }
}
function Leer-Clave($texto) {
    $s = Read-Host $texto -AsSecureString
    $b = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s)
    try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($b) } finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($b) }
}
# PowerShell 5.1 guarda en UTF-16 por defecto; los .ini deben quedar en UTF-8 sin BOM
function Guardar($ruta, $texto) {
    if ($PSCmdlet.ShouldProcess($ruta, 'Guardar cambios')) {
        [IO.File]::WriteAllText($ruta, $texto, (New-Object Text.UTF8Encoding($false)))
    }
}

$phpIni   = "$Xampp\php\php.ini"
$httpd    = "$Xampp\apache\conf\httpd.conf"
$myIni    = "$Xampp\mysql\bin\my.ini"
$pmaConf  = "$Xampp\phpMyAdmin\config.inc.php"
$mysql    = "$Xampp\mysql\bin\mysql.exe"
foreach ($f in $phpIni, $httpd, $myIni, $mysql) { if (-not (Test-Path $f)) { throw "No encuentro $f. ¿XAMPP está en $Xampp?" } }

Paso '1. Respaldos'
$phpIni, $httpd, $myIni, $pmaConf | ForEach-Object { Respaldar $_ }

Paso '2. php.ini: extensiones y ajustes seguros'
$ini = Get-Content $phpIni -Raw
foreach ($ext in 'intl', 'gd', 'mysqli', 'mbstring', 'zip', 'fileinfo') {
    # Solo líneas "extension=x" o ";extension=x" (sin espacio tras el ;): así no se tocan los ejemplos comentados
    $activa  = "(?m)^[ \t]*extension[ \t]*=[ \t]*`"?(php_)?$ext(\.dll)?`"?[ \t]*\r?$"
    $apagada = "(?m)^[ \t]*;extension[ \t]*=[ \t]*(`"?(php_)?$ext(\.dll)?`"?)[ \t]*(\r?)$"
    if ($ini -match $activa) { Write-Host "  extension=$ext ya activa" }
    elseif ($ini -match $apagada) {
        $ini = [regex]::Replace($ini, $apagada, 'extension=$1$4', 1)
        Write-Host "  extension=$ext activada"
    } else { Write-Host "  extension=$ext no aparece en php.ini (puede venir integrada)" -ForegroundColor Yellow }
}
$ini = $ini -replace '(?m)^[ \t]*;(zend_extension[ \t]*=[ \t]*"?(php_)?opcache(\.dll)?"?[ \t]*)(\r?)$', '$1$4'
# Ajustes recomendados por la hoja de configuración PHP de OWASP
$ajustes = [ordered]@{
    'expose_php' = 'Off'; 'display_errors' = 'Off'; 'display_startup_errors' = 'Off'; 'log_errors' = 'On'
    'allow_url_include' = 'Off'; 'session.cookie_httponly' = '1'; 'session.use_strict_mode' = '1'
    'session.use_only_cookies' = '1'; 'session.cookie_samesite' = 'Lax'
    'upload_max_filesize' = '10M'; 'post_max_size' = '12M'; 'date.timezone' = 'America/Guatemala'
}
foreach ($k in $ajustes.Keys) {
    $patron = '(?m)^[ \t]*;?[ \t]*' + [regex]::Escape($k) + '[ \t]*=[^\r\n]*'
    $linea = "$k = $($ajustes[$k])"
    if ($ini -match $patron) { $ini = [regex]::Replace($ini, $patron, $linea, 1) } else { $ini += "`r`n$linea" }
    Write-Host "  $linea"
}
Guardar $phpIni $ini

Paso '3. Apache y MariaDB solo en 127.0.0.1'
$h = Get-Content $httpd -Raw
$h = $h -replace '(?m)^Listen[ \t]+80[ \t]*(\r?)$', 'Listen 127.0.0.1:80$1'
$h = $h -replace '(?m)^ServerTokens[^\r\n]*', 'ServerTokens Prod'
if ($h -notmatch '(?m)^ServerTokens') { $h += "`r`nServerTokens Prod" }
if ($h -notmatch '(?m)^ServerSignature') { $h += "`r`nServerSignature Off" }
Guardar $httpd $h
Write-Host '  Apache: Listen 127.0.0.1:80, ServerTokens Prod, ServerSignature Off'
$m = Get-Content $myIni -Raw
# Solo la primera línea bind-address (XAMPP trae dos comentadas: IPv4 e IPv6)
if ($m -match '(?m)^[ \t]*#?[ \t]*bind-address[ \t]*=') { $m = [regex]::Replace($m, '(?m)^[ \t]*#?[ \t]*bind-address[ \t]*=[^\r\n]*', 'bind-address=127.0.0.1', 1) }
else { $m = [regex]::Replace($m, '(?m)^\[mysqld\][ \t]*\r?$', "[mysqld]`r`nbind-address=127.0.0.1`r", 1) }
Guardar $myIni $m
Write-Host '  MariaDB: bind-address=127.0.0.1'

Paso '4. Contraseña de root, phpMyAdmin y base de datos de osTicket'
$mysqld = "$Xampp\mysql\bin\mysqld.exe"
$arrancado = $null
if (-not (Get-Process mysqld -ErrorAction SilentlyContinue)) {
    if ($PSCmdlet.ShouldProcess('MariaDB', 'Iniciar temporalmente')) {
        $arrancado = Start-Process $mysqld -ArgumentList "--defaults-file=`"$myIni`"", '--standalone' -PassThru -WindowStyle Hidden
        Start-Sleep 6
    }
}
if ($PSCmdlet.ShouldProcess('MariaDB', 'Contraseña de root, base y usuario de osTicket')) {
    $actual = Leer-Clave 'Contraseña ACTUAL de root (Enter si nunca la cambiaste)'
    $root = Leer-Clave 'Nueva contraseña para root de MariaDB (guárdala en tu gestor de contraseñas)'
    $app  = Leer-Clave 'Contraseña para osticket_user (la pedirá el instalador de osTicket)'
    if ($root.Length -lt 12 -or $app.Length -lt 12) { throw 'Usa contraseñas de al menos 12 caracteres.' }
    function Sql($t) { $t.Replace('\', '\\').Replace("'", "\'") }
    # Archivo temporal de credenciales para no dejar la contraseña en el historial de comandos
    $tmp = New-TemporaryFile
    try {
        [IO.File]::WriteAllText($tmp, "[client]`r`nuser=root`r`npassword=`"$actual`"`r`nhost=localhost`r`n")
        $sql = @"
ALTER USER IF EXISTS 'root'@'localhost' IDENTIFIED BY '$(Sql $root)';
ALTER USER IF EXISTS 'root'@'127.0.0.1' IDENTIFIED BY '$(Sql $root)';
ALTER USER IF EXISTS 'root'@'::1' IDENTIFIED BY '$(Sql $root)';
DROP USER IF EXISTS ''@'localhost';
DROP DATABASE IF EXISTS test;
CREATE DATABASE IF NOT EXISTS osticket_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS 'osticket_user'@'localhost' IDENTIFIED BY '$(Sql $app)';
ALTER USER 'osticket_user'@'localhost' IDENTIFIED BY '$(Sql $app)';
GRANT ALL PRIVILEGES ON osticket_db.* TO 'osticket_user'@'localhost';
FLUSH PRIVILEGES;
"@
        $sql | & $mysql "--defaults-extra-file=$tmp"
        if ($LASTEXITCODE) { throw 'MariaDB devolvió un error (¿la contraseña actual de root es correcta?).' }
        Write-Host '  root con contraseña (localhost, 127.0.0.1 y ::1), usuario anónimo y base test eliminados'
        Write-Host '  osticket_db (utf8mb4) y osticket_user con permisos solo sobre osticket_db'
    } finally { Remove-Item $tmp -Force }
}
if ($arrancado) { Stop-Process $arrancado.Id -Force }

if (Test-Path $pmaConf) {
    $p = Get-Content $pmaConf -Raw
    $p = $p -replace "\['auth_type'\]\s*=\s*'config'", "['auth_type'] = 'cookie'"
    $p = $p -replace "(\['password'\]\s*=\s*)'[^']*'", "`$1''"
    if ($p -notmatch "blowfish_secret'\]\s*=\s*'.{32,}'") {
        $secreto = -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 32 | ForEach-Object { [char]$_ })
        if ($p -match "blowfish_secret") { $p = $p -replace "(\['blowfish_secret'\]\s*=\s*)'[^']*'", "`$1'$secreto'" }
        else { $p = $p -replace '\?>\s*$', "`$cfg['blowfish_secret'] = '$secreto';`r`n?>" }
    }
    Guardar $pmaConf $p
    Write-Host '  phpMyAdmin: inicio de sesión con formulario (ya no entra solo como root)'
}

Write-Host "`nListo. Inicia Apache y MySQL en el XAMPP Control Panel y prueba http://localhost" -ForegroundColor Green
