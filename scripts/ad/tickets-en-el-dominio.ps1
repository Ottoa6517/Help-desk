<#
.SYNOPSIS
  Hace de verdad, en el dominio dailybrew.local del Proyecto 1, lo que piden los tickets 2, 7, 15 y 20.
.DESCRIPTION
  Ejecutar en DC01 como administrador del dominio. Cada ticket es una función aparte para que
  puedas tomar la captura de su salida y pegarla en la nota interna del ticket en osTicket.
  Admite -WhatIf para ver qué haría sin cambiar nada.
.EXAMPLE
  .\tickets-en-el-dominio.ps1 -Ticket 7 -WhatIf
  .\tickets-en-el-dominio.ps1 -Ticket 15
#>
[CmdletBinding(SupportsShouldProcess)]
param([Parameter(Mandatory)][ValidateSet(2, 7, 15, 20)][int]$Ticket)

Import-Module ActiveDirectory
$ErrorActionPreference = 'Stop'
$dom  = (Get-ADDomain).DistinguishedName          # DC=dailybrew,DC=local
$raiz = "OU=DailyBrew,$dom"

function Clave-Temporal {
    # La contraseña temporal se dice al usuario EN PERSONA; nunca se manda por correo ni queda en el ticket
    Read-Host 'Contraseña temporal (díctala al usuario en persona)' -AsSecureString
}

switch ($Ticket) {
    2 {
        Write-Host '== Ticket 2: Luis olvidó su contraseña ==' -ForegroundColor Cyan
        Write-Host 'Antes: verifica su identidad en persona (gafete) o con su supervisor.'
        $u = Get-ADUser lcifuentes -Properties LockedOut, PasswordLastSet
        $u | Format-List SamAccountName, Enabled, LockedOut, PasswordLastSet
        if ($PSCmdlet.ShouldProcess('lcifuentes', 'Restablecer contraseña y forzar cambio')) {
            Set-ADAccountPassword lcifuentes -Reset -NewPassword (Clave-Temporal)
            Set-ADUser lcifuentes -ChangePasswordAtLogon $true
            if ($u.LockedOut) { Unlock-ADAccount lcifuentes }
        }
        Get-ADUser lcifuentes -Properties pwdLastSet | Select-Object SamAccountName, @{n = 'DebeCambiarClave'; e = { $_.pwdLastSet -eq 0 } }
    }
    7 {
        Write-Host '== Ticket 7: cuenta bloqueada de José (jhernandez) ==' -ForegroundColor Cyan
        Search-ADAccount -LockedOut | Format-Table SamAccountName, LastLogonDate -AutoSize
        # Evento 4740 = cuenta bloqueada; CallerComputerName dice desde qué equipo fueron los intentos
        Get-WinEvent -FilterHashtable @{ LogName = 'Security'; Id = 4740; StartTime = (Get-Date).AddDays(-1) } -ErrorAction SilentlyContinue |
            Where-Object { $_.Properties[0].Value -eq 'jhernandez' } |
            Select-Object TimeCreated, @{n = 'Usuario'; e = { $_.Properties[0].Value } }, @{n = 'EquipoOrigen'; e = { $_.Properties[1].Value } } |
            Format-Table -AutoSize
        Write-Host 'Si el equipo de origen NO es el del usuario, o fue fuera de horario: tratarlo como incidente de Seguridad y escalar.' -ForegroundColor Yellow
        if ($PSCmdlet.ShouldProcess('jhernandez', 'Desbloquear')) { Unlock-ADAccount jhernandez }
        Get-ADUser jhernandez -Properties LockedOut | Format-List SamAccountName, LockedOut
    }
    15 {
        Write-Host '== Ticket 15: acceso de José a la carpeta Inventario (aprobado por Carlos) ==' -ForegroundColor Cyan
        $ouAdmin = "OU=Administracion,$raiz"
        if (-not (Get-ADGroup -Filter "Name -eq 'GG_Inventario'")) {
            if ($PSCmdlet.ShouldProcess('GG_Inventario', 'Crear grupo')) {
                New-ADGroup GG_Inventario -GroupScope Global -GroupCategory Security -Path $ouAdmin -Description 'Acceso de modificación a \\dc01\Compartido\Inventario'
            }
        }
        $carpeta = 'C:\Compartido\Inventario'
        if ($PSCmdlet.ShouldProcess($carpeta, 'Crear carpeta y dar Modificar a GG_Inventario')) {
            New-Item $carpeta -ItemType Directory -Force | Out-Null
            icacls $carpeta /inheritance:d | Out-Null                          # deja de heredar "Domain Users: Modificar"
            icacls $carpeta /remove:g 'DAILYBREW\Domain Users' | Out-Null      # mínimo privilegio
            icacls $carpeta /grant 'DAILYBREW\GG_Inventario:(OI)(CI)M' | Out-Null
            Add-ADGroupMember GG_Inventario -Members jhernandez
        }
        icacls $carpeta
        Get-ADGroupMember GG_Inventario | Select-Object SamAccountName
        Write-Host 'Pide a José cerrar y abrir sesión: los grupos se cargan al iniciar sesión.' -ForegroundColor Yellow
    }
    20 {
        Write-Host '== Ticket 20: alta de Hugo Salazar (Bodega) ==' -ForegroundColor Cyan
        Write-Host 'Corrige el error que dejó a propósito la carga masiva del Proyecto 1 (no existía la OU Bodega).'
        if (-not (Get-ADOrganizationalUnit -Filter "Name -eq 'Bodega'" -SearchBase $raiz -SearchScope OneLevel)) {
            if ($PSCmdlet.ShouldProcess('OU=Bodega', 'Crear OU y grupo GG_Bodega')) {
                New-ADOrganizationalUnit Bodega -Path $raiz
                New-ADGroup GG_Bodega -GroupScope Global -GroupCategory Security -Path "OU=Bodega,$raiz" -Description 'Personal de Bodega de Daily Brew'
            }
        }
        if (-not (Get-ADUser -Filter "SamAccountName -eq 'hsalazar'")) {
            if ($PSCmdlet.ShouldProcess('hsalazar', 'Crear usuario')) {
                New-ADUser 'Hugo Salazar' -GivenName Hugo -Surname Salazar -SamAccountName hsalazar `
                    -UserPrincipalName 'hsalazar@dailybrew.local' -Department Bodega -Path "OU=Bodega,$raiz" `
                    -AccountPassword (Clave-Temporal) -ChangePasswordAtLogon $true -Enabled $true
                Add-ADGroupMember GG_Bodega -Members hsalazar
                if (Get-ADGroup -Filter "Name -eq 'GG_Inventario'") { Add-ADGroupMember GG_Inventario -Members hsalazar }
            }
        }
        Get-ADUser hsalazar -Properties Department, MemberOf |
            Format-List SamAccountName, DistinguishedName, Department, Enabled, @{n = 'Grupos'; e = { ($_.MemberOf | ForEach-Object { ($_ -split ',')[0] -replace 'CN=' }) -join ', ' } }
        Write-Host 'En el equipo nuevo (como administrador): Add-Computer -DomainName dailybrew.local -OUPath "OU=Equipos,OU=DailyBrew,DC=dailybrew,DC=local" -Restart' -ForegroundColor Yellow
        Write-Host 'Después de que Hugo inicie sesión: gpresult /r  (deben aparecer las GPO de fondo y unidad S:)' -ForegroundColor Yellow
    }
}
