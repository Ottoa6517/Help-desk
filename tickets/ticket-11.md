# Ticket 11: Excel se cierra solo

| Campo | Valor |
|---|---|
| Usuario | Andrés Soto (Contabilidad), cuenta de dominio `DAILYBREW\asoto` |
| Tema de ayuda | Software |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> Abro el archivo de costos y Excel se cierra sin ningún mensaje.

## Nota interna

```
SÍNTOMA: Excel se cierra al abrir cualquier libro, sin mensaje de error.
DIAGNÓSTICO: Visor de eventos > Aplicación: evento 1000 (Application Error) de EXCEL.EXE con un módulo de un complemento de PDF de terceros. Con excel /safe (modo seguro, sin complementos) abre bien.
CAUSA RAÍZ: Complemento COM dañado.
SOLUCIÓN: Archivo > Opciones > Complementos > Administrar: Complementos COM > desmarqué el complemento de PDF. Excel abrió normal. Hice además Reparación rápida de Office desde Aplicaciones instaladas.
PREVENCIÓN: Instalar complementos solo de la lista aprobada; Office ya exporta a PDF sin complementos.
TIEMPO: 25 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Andrés: el problema era un complemento de PDF que se dañó y hacía cerrar Excel. Lo desactivamos y reparamos Office. Para guardar en PDF usa Archivo > Exportar > PDF, no necesitas el complemento.

## Artículo de base de conocimiento

[kb07-excel-se-cierra-solo.md](../base-de-conocimiento/kb07-excel-se-cierra-solo.md)
