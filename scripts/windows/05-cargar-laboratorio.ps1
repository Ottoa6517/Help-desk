<#
.SYNOPSIS
  Corre las fases 2 a 6 del laboratorio con el PHP de XAMPP (scripts\osticket-lab.php).
.EXAMPLE
  # Todo de una vez
  powershell -ExecutionPolicy Bypass -File .\scripts\windows\05-cargar-laboratorio.ps1 -Fase todo
  # O fase por fase, para tomar capturas entre una y otra
  .\scripts\windows\05-cargar-laboratorio.ps1 -Fase configurar
  .\scripts\windows\05-cargar-laboratorio.ps1 -Fase tickets -Solo 1,2,3
#>
param(
    [ValidateSet('configurar', 'usuarios', 'tickets', 'resolver', 'kb', 'cronologia', 'metricas', 'todo')]
    [string]$Fase = 'todo',
    [string]$Xampp = 'C:\xampp',
    [string]$Admin = 'otto.admin',
    [int[]]$Solo,
    [switch]$Seco
)
$ErrorActionPreference = 'Stop'
$php = "$Xampp\php\php.exe"
$script = Join-Path $PSScriptRoot '..\osticket-lab.php'
$args2 = @($script, $Fase, "--osticket=$Xampp\htdocs\osticket", "--admin=$Admin")
if ($Solo) { $args2 += "--solo=$($Solo -join ',')" }
if ($Seco) { $args2 += '--seco' }
# Consola en UTF-8 para que se vean bien las tildes
[Console]::OutputEncoding = [Text.Encoding]::UTF8
& $php @args2
exit $LASTEXITCODE
