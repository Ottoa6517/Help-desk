# Ticket 10: El Wi-Fi de la oficina es lento

| Campo | Valor |
|---|---|
| Usuario | Carlos Méndez (Gerente de tienda), cuenta de dominio `DAILYBREW\cmendez` |
| Tema de ayuda | Red e internet |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> En las tardes el Wi-Fi de la oficina es lentísimo, las videollamadas se cortan.

## Nota interna

```
SÍNTOMA: Wi-Fi lento por las tardes; videollamadas que se cortan.
DIAGNÓSTICO: Con un analizador Wi-Fi: el router usa el canal 6 de 2,4 GHz, compartido con 8 redes vecinas. El router tiene 31 dispositivos conectados: los clientes de la cafetería usan la misma red que la oficina. La banda de 5 GHz está apagada.
CAUSA RAÍZ: Interferencia en el canal y red compartida entre clientes y personal.
SOLUCIÓN: Fuera del horario de atención (19:00): cambié 2,4 GHz al canal 11, activé 5 GHz para el personal y creé la red DailyBrew-Clientes con aislamiento de clientes y límite de 5 Mbps por dispositivo. Al día siguiente la prueba de velocidad en la oficina pasó de 3 a 48 Mbps.
PREVENCIÓN: Revisar canales cada trimestre. Nota: el SLA se venció porque el cambio se programó fuera de horario para no cortar ventas; debió pausarse el SLA o acordarse con el usuario.
TIEMPO: 60 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Carlos: el Wi-Fi estaba saturado porque los clientes y la oficina compartían la misma red y el canal estaba lleno de redes vecinas. Separamos la red de clientes y movimos la oficina a 5 GHz. La velocidad pasó de 3 a 48 Mbps.

## Artículo de base de conocimiento

[kb06-wifi-lento.md](../base-de-conocimiento/kb06-wifi-lento.md)
