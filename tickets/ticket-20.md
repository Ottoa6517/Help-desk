# Ticket 20: Usuario nuevo: preparar su computadora

| Campo | Valor |
|---|---|
| Usuario | Paola Recinos (Marketing), cuenta de dominio `DAILYBREW\precinos` |
| Tema de ayuda | Software |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> El lunes entra Hugo Salazar a Bodega. Necesita usuario, correo y computadora lista.

## Nota interna

```
SÍNTOMA: Solicitud de alta (onboarding) para un empleado nuevo de Bodega.
DIAGNÓSTICO: En el dominio no existía la OU Bodega (por eso la carga masiva del Proyecto 1 marcó error en hsalazar). Carlos confirmó puesto y fecha de ingreso.
CAUSA RAÍZ: Solicitud de servicio: alta de usuario.
SOLUCIÓN: En DC01: New-ADOrganizationalUnit Bodega bajo OU=DailyBrew, grupo GG_Bodega, New-ADUser hsalazar con -ChangePasswordAtLogon $true y alta en GG_Bodega y GG_Inventario. Uní el equipo al dominio con Add-Computer y lo moví a la OU Equipos. Instalé Microsoft 365 Apps y verifiqué con gpresult /r que aplican las GPO de fondo y unidad S:.
PREVENCIÓN: Crear una lista de verificación de onboarding y pedir los datos con 2 días hábiles de anticipación.
TIEMPO: 60 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Paola: Hugo ya tiene usuario, correo y computadora lista. El lunes debe iniciar sesión con la contraseña temporal que le daremos en persona y crear la suya.
