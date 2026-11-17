# Ticket 2: Olvidé mi contraseña

| Campo | Valor |
|---|---|
| Usuario | Luis Cifuentes (Barista), cuenta de dominio `DAILYBREW\lcifuentes` |
| Tema de ayuda | Cuentas y accesos |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | En persona |
| Escalado | No |

## Lo que escribió el usuario

> Volví de vacaciones y no me acuerdo de mi contraseña de la computadora.

## Nota interna

```
SÍNTOMA: Luis no puede iniciar sesión en el dominio después de sus vacaciones.
DIAGNÓSTICO: Verifiqué su identidad en persona con su gafete y con su supervisor presente. La cuenta lcifuentes no está bloqueada (Search-ADAccount -LockedOut no la lista), solo olvidó la clave.
CAUSA RAÍZ: Contraseña olvidada.
SOLUCIÓN: En DC01: Set-ADAccountPassword lcifuentes -Reset con una clave temporal dicha en persona, y Set-ADUser lcifuentes -ChangePasswordAtLogon $true. Luis inició sesión y creó su nueva contraseña.
PREVENCIÓN: Habilitar autoservicio de restablecimiento de contraseña (SSPR) para que el usuario lo haga solo tras verificar su identidad.
TIEMPO: 10 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Luis: tu contraseña quedó restablecida y ya creaste la nueva. Recuerda: una frase larga que puedas recordar y que no uses en ningún otro sitio.

## Artículo de base de conocimiento

[kb01-olvide-mi-contrasena.md](../base-de-conocimiento/kb01-olvide-mi-contrasena.md)
