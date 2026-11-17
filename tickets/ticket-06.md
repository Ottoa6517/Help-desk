# Ticket 6: Laptop muy lenta

| Campo | Valor |
|---|---|
| Usuario | Paola Recinos (Marketing), cuenta de dominio `DAILYBREW\precinos` |
| Tema de ayuda | Hardware |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> La laptop tarda como 10 minutos en encender y todo se congela cuando abro Canva y el correo a la vez.

## Nota interna

```
SÍNTOMA: Arranque de unos 10 minutos y congelamientos con varias aplicaciones abiertas.
DIAGNÓSTICO: Administrador de tareas: disco al 100 % durante el arranque y 14 aplicaciones habilitadas en Inicio. El disco C: tenía 3 GB libres de 237 GB (la carpeta Descargas ocupaba 60 GB).
CAUSA RAÍZ: Disco casi lleno y demasiados programas al iniciar.
SOLUCIÓN: Con la usuaria: moví Descargas a OneDrive y borré instaladores viejos; Liberador de espacio (incluyendo archivos de sistema). Deshabilité 10 aplicaciones de inicio que no necesita. Resultado: 48 GB libres y arranque en 1 min 20 s.
PREVENCIÓN: Activar Sensor de almacenamiento para que limpie temporales y la papelera cada mes.
TIEMPO: 40 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Paola: la laptop tenía el disco casi lleno y muchos programas arrancando solos. Liberamos 45 GB y quitamos lo que no necesitabas al inicio. Ahora enciende en poco más de un minuto.

## Artículo de base de conocimiento

[kb04-computadora-lenta.md](../base-de-conocimiento/kb04-computadora-lenta.md)
