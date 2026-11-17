# Ticket 1: No hay internet en caja

| Campo | Valor |
|---|---|
| Usuario | María García (Cajera de barra), cuenta de dominio `DAILYBREW\mgarcia` |
| Tema de ayuda | Red e internet |
| Prioridad | Emergency |
| Plan de SLA | Crítico - 4h |
| Canal | Teléfono |
| Escalado | Sí, a Nivel 2 |

## Lo que escribió el usuario

> La caja no puede cobrar con tarjeta, dice que no hay conexión. Ya hay fila de clientes.

## Nota interna

```
SÍNTOMA: La caja no procesa pagos con tarjeta; ningún equipo de la tienda tiene internet.
DIAGNÓSTICO: El router tiene luz LOS en rojo y PON apagada. Reinicié el router (30 s sin corriente): misma luz. La red local funciona (la caja hace ping a 192.168.1.1), pero no a 8.8.8.8.
CAUSA RAÍZ: Corte de fibra óptica del proveedor de internet en la zona.
SOLUCIÓN: Contingencia en 10 min: conecté la caja al hotspot 4G del celular de la tienda para seguir cobrando. Escalé a Nivel 2, que abrió el caso con el ISP. El proveedor reparó la fibra y la conexión volvió; quité el hotspot y probé un cobro de Q1.00: OK.
PREVENCIÓN: Proponer un segundo enlace 4G de respaldo en el router (failover automático) y dejar el número del ISP pegado junto al router.
TIEMPO: 35 min | ESCALADO: Sí
```

## Por qué se escaló

El reinicio no lo resolvió y la luz LOS del router está en rojo: es un corte de fibra del proveedor. Nivel 2 tiene el contrato y el número de soporte del ISP.

## Respuesta al usuario

> Hola María: el internet ya volvió. Fue un corte de fibra del proveedor en la zona, no algo de la tienda. Mientras lo reparaban dejamos la caja conectada al celular para que pudieras seguir cobrando. Si vuelve a pasar, llama directo a la extensión 100.
