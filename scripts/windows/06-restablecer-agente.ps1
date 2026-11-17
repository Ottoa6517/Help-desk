<#
.SYNOPSIS
  Restablece la contraseña de un agente de osTicket (por ejemplo, si olvidaste la de otto.admin).
.EXAMPLE
  powershell -ExecutionPolicy Bypass -File .\scripts\windows\06-restablecer-agente.ps1 -Usuario otto.admin
#>
param([string]$Usuario = 'otto.admin', [string]$Xampp = 'C:\xampp')
$ErrorActionPreference = 'Stop'
function Leer($t) {
    $s = Read-Host $t -AsSecureString
    $b = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($s)
    try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($b) } finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($b) }
}
$a = Leer "Nueva contraseña para $Usuario (mínimo 12 caracteres)"
$b = Leer 'Repítela'
if ($a -ne $b) { throw 'No coinciden.' }
if ($a.Length -lt 12) { throw 'Debe tener al menos 12 caracteres.' }
$env:OST_NUEVA = $a
try { & "$Xampp\php\php.exe" (Join-Path $PSScriptRoot '..\restablecer-agente.php') "$Xampp\htdocs\osticket" $Usuario }
finally { Remove-Item Env:\OST_NUEVA -ErrorAction SilentlyContinue }
