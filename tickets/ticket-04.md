# Ticket 4: La impresora no imprime

| Campo | Valor |
|---|---|
| Usuario | Andrés Soto (Contabilidad), cuenta de dominio `DAILYBREW\asoto` |
| Tema de ayuda | Hardware |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> Mando a imprimir las facturas y no sale nada. La impresora dice Lista.

## Nota interna

```
SÍNTOMA: Andrés manda a imprimir facturas y no sale nada; la impresora muestra Lista.
DIAGNÓSTICO: En Configuración > Impresoras había 6 trabajos atascados. El primero estaba en Error.
CAUSA RAÍZ: Un trabajo dañado bloqueó la cola de impresión.
SOLUCIÓN: services.msc > Print Spooler > Detener. Borré los archivos de C:\Windows\System32\spool\PRINTERS. Inicié el servicio y envié página de prueba: OK.
PREVENCIÓN: Si se repite, actualizar el driver de la impresora desde la página del fabricante.
TIEMPO: 12 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Andrés: un documento dañado había trabado la cola de impresión. La limpiamos y la impresora ya funciona. Reenvía las facturas que no salieron.

## Artículo de base de conocimiento

[kb03-impresora-no-imprime.md](../base-de-conocimiento/kb03-impresora-no-imprime.md)
