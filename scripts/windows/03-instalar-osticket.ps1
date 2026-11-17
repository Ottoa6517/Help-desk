<#
.SYNOPSIS
  Descarga osTicket 1.18.4, verifica su integridad, lo instala y lo asegura (fases 1.4 a 1.6).
.DESCRIPTION
  1. Descarga el ZIP oficial desde la página de versiones de osTicket en GitHub.
  2. Compara su SHA-256 con el valor fijado abajo. Si no coincide, se detiene: el archivo
     no es el que se revisó.
  3. Copia la carpeta upload a htdocs\osticket y crea ost-config.php.
  4. Corre el instalador web por ti (mismos campos del paso 1.5).
  5. Borra la carpeta setup y deja ost-config.php en solo lectura (paso 1.6).
  Requiere Apache y MySQL encendidos y haber corrido 02-preparar-xampp.ps1.
.EXAMPLE
  powershell -ExecutionPolicy Bypass -File .\scripts\windows\03-instalar-osticket.ps1 -Nombre Otto -Apellido "Aragón" -Correo otto.admin@dailybrew.local
#>
[CmdletBinding(SupportsShouldProcess)]
param(
    [string]$Xampp = 'C:\xampp',
    [string]$Url = 'http://localhost/osticket',
    [Parameter(Mandatory)][string]$Nombre,
    [Parameter(Mandatory)][string]$Apellido,
    [Parameter(Mandatory)][string]$Correo,
    [string]$Usuario = 'otto.admin'
)
$ErrorActionPreference = 'Stop'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$Version = 'v1.18.4'
# SHA-256 del ZIP oficial osTicket-v1.18.4.zip, calculado y revisado el 17/11/2026
# (el contenido de upload/ coincide con la etiqueta v1.18.4 del repositorio oficial).
$Sha256  = 'A4187DF5F52DD41625E6B61B322245853542E12B5969A45F0A6D81F7AB8151A7'
$Zip     = "$env:TEMP\osTicket-$Version.zip"
$Destino = "$Xampp\htdocs\osticket"

function Leer-Clave($texto) {
    $s = Read-Host $texto -AsSecureString
    $b = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s)
    try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($b) } finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($b) }
}

Write-Host "== 1. Descarga y verificación ==" -ForegroundColor Cyan
if (-not (Test-Path $Zip)) {
    Invoke-WebRequest "https://github.com/osTicket/osTicket/releases/download/$Version/osTicket-$Version.zip" -OutFile $Zip -UseBasicParsing
}
$hash = (Get-FileHash $Zip -Algorithm SHA256).Hash
if ($hash -ne $Sha256) {
    Remove-Item $Zip -Force
    throw "El SHA-256 no coincide ($hash). No se instala nada. Descarga de nuevo o revisa la versión."
}
Write-Host "  SHA-256 verificado: $hash"

Write-Host "== 2. Copia a $Destino ==" -ForegroundColor Cyan
if (Test-Path $Destino) { throw "$Destino ya existe. Renómbralo o bórralo si quieres reinstalar." }
if ($PSCmdlet.ShouldProcess($Destino, 'Instalar osTicket')) {
    $tmp = Join-Path $env:TEMP "osticket-$([guid]::NewGuid())"
    Expand-Archive $Zip $tmp
    Move-Item "$tmp\upload" $Destino
    Remove-Item $tmp -Recurse -Force
    Copy-Item "$Destino\include\ost-sampleconfig.php" "$Destino\include\ost-config.php"
    Write-Host '  Archivos copiados y ost-config.php creado'
}

Write-Host '== 3. Instalador web ==' -ForegroundColor Cyan
if ($PSCmdlet.ShouldProcess($Url, 'Correr instalador')) {
    $admin = Leer-Clave "Contraseña para el administrador $Usuario (mínimo 12 caracteres)"
    $db    = Leer-Clave 'Contraseña de osticket_user (la del script 02)'
    if ($admin.Length -lt 12) { throw 'La contraseña del administrador debe tener al menos 12 caracteres.' }
    $s = $null
    $u = "$Url/setup/install.php"
    Invoke-WebRequest $u -SessionVariable s -UseBasicParsing | Out-Null
    Invoke-WebRequest $u -WebSession $s -Method Post -Body @{ s = 'prereq' } -UseBasicParsing | Out-Null
    Invoke-WebRequest $u -WebSession $s -Method Post -Body @{ s = 'config' } -UseBasicParsing | Out-Null
    $r = Invoke-WebRequest $u -WebSession $s -Method Post -UseBasicParsing -Body @{
        s = 'install'; name = 'Daily Brew - Soporte de TI'; email = 'soporte@dailybrew.local'; lang_id = 'en_US'
        fname = $Nombre; lname = $Apellido; admin_email = $Correo; username = $Usuario
        passwd = $admin; passwd2 = $admin; prefix = 'ost_'; dbhost = 'localhost'
        dbname = 'osticket_db'; dbuser = 'osticket_user'; dbpass = $db; timezone = 'America/Guatemala'
    }
    if ($r.Content -notmatch 'Congratulations') {
        $err = [regex]::Matches($r.Content, 'class="error"[^>]*>([^<]+)') | ForEach-Object { $_.Groups[1].Value.Trim() } | Where-Object { $_ }
        throw "El instalador no terminó. Mensajes: $($err -join ' | '). Puedes abrir $u en el navegador para verlo."
    }
    Write-Host '  Instalación correcta. Toma la captura del mensaje si lo abres en el navegador.' -ForegroundColor Green
}

Write-Host '== 4. Asegurar la instalación (paso 1.6) ==' -ForegroundColor Cyan
if ($PSCmdlet.ShouldProcess($Destino, 'Borrar setup y proteger ost-config.php')) {
    Remove-Item "$Destino\setup" -Recurse -Force
    Set-ItemProperty "$Destino\include\ost-config.php" -Name IsReadOnly -Value $true
    Write-Host '  setup borrada y ost-config.php en solo lectura'
}
Write-Host "`nPortal de clientes: $Url`nPanel de agentes:   $Url/scp" -ForegroundColor Green
