# Ticket 7: Cuenta bloqueada

| Campo | Valor |
|---|---|
| Usuario | José Hernández (Bodega), cuenta de dominio `DAILYBREW\jhernandez` |
| Tema de ayuda | Cuentas y accesos |
| Prioridad | High |
| Plan de SLA | Normal - 24h |
| Canal | Teléfono |
| Escalado | No |

## Lo que escribió el usuario

> No me deja entrar, dice que mi cuenta está bloqueada. Tengo que registrar la mercadería que llegó.

## Nota interna

```
SÍNTOMA: Mensaje 'La cuenta a la que se hace referencia está bloqueada'.
DIAGNÓSTICO: Search-ADAccount -LockedOut lista a jhernandez. En el Visor de eventos de DC01 (registro Security) el evento 4740 indica que el bloqueo vino de CLIENTE01, el equipo de José, y los 4625 previos son 3 intentos seguidos en 1 minuto. Le pregunté y confirmó que se equivocó al escribir la clave.
CAUSA RAÍZ: Tres intentos fallidos alcanzaron el umbral de bloqueo de la política del dominio. Origen legítimo, no hay indicio de ataque.
SOLUCIÓN: Unlock-ADAccount jhernandez. José inició sesión de inmediato.
PREVENCIÓN: Si un bloqueo viene de un equipo que no es del usuario o fuera de horario, tratarlo como incidente de seguridad y escalarlo.
TIEMPO: 15 min | ESCALADO: No
```

## Respuesta al usuario

> Hola José: tu cuenta se bloqueó porque se escribió mal la contraseña tres veces seguidas desde tu equipo. Ya la desbloqueamos. Si vuelve a pasar sin que te hayas equivocado, avísanos de inmediato.
