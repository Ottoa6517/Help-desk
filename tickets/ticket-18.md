# Ticket 18: No me conecto a la VPN

| Campo | Valor |
|---|---|
| Usuario | Andrés Soto (Contabilidad), cuenta de dominio `DAILYBREW\asoto` |
| Tema de ayuda | Red e internet |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | Sí, a Nivel 2 |

## Lo que escribió el usuario

> Trabajo desde casa los viernes y hoy la VPN no conecta, dice error de autenticación.

## Nota interna

```
SÍNTOMA: La VPN falla con 'error de autenticación' desde casa.
DIAGNÓSTICO: El usuario y la contraseña funcionan en el correo, así que no son las credenciales. La versión del cliente de VPN es anterior a la actualización de firmware que se aplicó al firewall esta semana, y el registro del cliente muestra un error de negociación.
CAUSA RAÍZ: Cliente de VPN desactualizado e incompatible con el firmware nuevo del firewall.
SOLUCIÓN: Escalé a Nivel 2. Generó un perfil nuevo y publicó la versión actualizada del cliente. Lo instalé con Andrés por sesión remota y conectó. El SLA se venció porque Nivel 2 tenía otro caso crítico.
PREVENCIÓN: Antes de actualizar el firewall, actualizar los clientes de VPN y avisar a quienes trabajan remoto.
TIEMPO: 30 min | ESCALADO: Sí
```

## Por qué se escaló

El cliente de VPN necesita un perfil nuevo del firewall y solo Nivel 2 tiene acceso a la consola del firewall.

## Respuesta al usuario

> Hola Andrés: la VPN fallaba porque el programa de tu computadora quedó viejo después de una actualización del firewall. Ya lo actualizamos y conectas normal. Disculpa la demora.

## Artículo de base de conocimiento

[kb10-no-me-conecto-a-la-vpn.md](../base-de-conocimiento/kb10-no-me-conecto-a-la-vpn.md)
