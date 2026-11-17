# Ticket 17: El antivirus detectó una amenaza

| Campo | Valor |
|---|---|
| Usuario | Carlos Méndez (Gerente de tienda), cuenta de dominio `DAILYBREW\cmendez` |
| Tema de ayuda | Seguridad |
| Prioridad | Emergency |
| Plan de SLA | Crítico - 4h |
| Canal | Teléfono |
| Escalado | Sí, a Nivel 2 |

## Lo que escribió el usuario

> Me salió una alerta roja del antivirus después de abrir una factura que me mandaron por correo.

## Nota interna

```
SÍNTOMA: Alerta de Microsoft Defender tras abrir un adjunto 'factura_pdf.exe'.
DIAGNÓSTICO: Protección contra virus y amenazas > Historial: Trojan detectado y puesto en cuarentena en Descargas. El archivo era un ejecutable disfrazado de PDF.
CAUSA RAÍZ: Adjunto malicioso en un correo de un falso proveedor.
SOLUCIÓN: Aislé el equipo de la red (cable y Wi-Fi) sin apagarlo. Ejecuté un examen completo de Defender: solo la detección ya en cuarentena. Escalé a Nivel 2, que revisó los demás equipos y confirmó que no hubo propagación. Por precaución se restablecieron las contraseñas usadas en ese equipo. Todo quedó documentado en este ticket.
PREVENCIÓN: Bloquear adjuntos .exe en el correo, mostrar extensiones de archivo en Windows y capacitar sobre facturas falsas.
TIEMPO: 45 min | ESCALADO: Sí
```

## Por qué se escaló

Posible malware ejecutado en el equipo del gerente: requiere análisis forense, revisar otros equipos y decidir si se reinstala.

## Respuesta al usuario

> Hola Carlos: el antivirus bloqueó el archivo a tiempo. Revisamos tu equipo y los demás y no hay rastro de infección; por precaución cambiamos tus contraseñas. Ese correo de 'factura' era falso: si te llega algo parecido, no lo abras y reenvíanoslo.
