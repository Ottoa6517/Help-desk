# Ticket 16: Windows pide reiniciar por actualizaciones

| Campo | Valor |
|---|---|
| Usuario | Elena Ruiz (Atención al cliente), cuenta de dominio `DAILYBREW\eruiz` |
| Tema de ayuda | Software |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> Me sale a cada rato que tengo que reiniciar por actualizaciones, pero estoy atendiendo clientes.

## Nota interna

```
SÍNTOMA: Avisos repetidos de reinicio pendiente durante el horario de atención.
DIAGNÓSTICO: Configuración > Windows Update: actualización acumulativa pendiente de reinicio desde hace 4 días. Horas activas sin configurar.
CAUSA RAÍZ: Reinicio pendiente que la usuaria pospone por estar atendiendo.
SOLUCIÓN: Configuré horas activas de 07:00 a 19:00 y programé el reinicio a las 19:30. Al día siguiente revisé el Historial de actualizaciones: instalada correctamente.
PREVENCIÓN: Configurar las horas activas por directiva de grupo para todos los equipos de atención.
TIEMPO: 10 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Elena: dejamos programado el reinicio para después de tu turno y la actualización ya quedó instalada. Ya no te debería interrumpir en horario de atención.
