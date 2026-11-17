# Postmortem: adjunto malicioso en el equipo del gerente (ticket 17)

**Fecha:** 11/11/2026 (simulado) | **Duración:** 3 h 30 min, de la llamada al cierre | **Severidad:** SEV2
**Autor:** Otto Aragón, Soporte Nivel 1 | **Estado:** Final | **Formato:** sin culpables (blameless)

> Incidente ficticio del laboratorio. La línea de tiempo coincide con la cronología simulada del ticket 17 en osTicket.

## Resumen

El gerente de tienda abrió un adjunto llamado `factura_pdf.exe` que llegó en un correo de un supuesto proveedor. Microsoft Defender lo detectó como troyano y lo puso en cuarentena antes de que hiciera daño. Nivel 1 aisló el equipo, lo escaneó y escaló a Nivel 2, que confirmó que no hubo propagación a otros equipos.

## Impacto

- 1 equipo (el del gerente) fuera de la red durante unas 3 horas.
- Sin pérdida de datos ni indicios de robo de credenciales. Por precaución se cambiaron las contraseñas usadas en ese equipo.
- Ventas no afectadas: la caja y los demás equipos siguieron operando.

## Línea de tiempo (hora de Guatemala)

| Hora | Evento |
|---|---|
| 09:05 | Carlos abre el adjunto; Defender muestra la alerta roja |
| 09:10 | Carlos llama a la extensión 100. Se abre el ticket 17: tema Seguridad, prioridad Emergency, SLA Crítico 4 h |
| 09:13 | Acuse de recibo. Se le pide no apagar el equipo y desconectar cable y Wi-Fi |
| 09:15 | Equipo aislado de la red (contención) |
| 09:54 | Historial de Defender: troyano en cuarentena en Descargas. Examen completo sin otras detecciones. Nota interna registrada |
| 10:36 | Escalado al equipo Nivel 2 con el motivo documentado |
| 11:59 | Nivel 2 revisa los demás equipos: sin detecciones. Bloquea el remitente para toda la empresa |
| 12:20 | Cambio de contraseñas usadas en el equipo; equipo reconectado |
| 12:40 | Respuesta al usuario y ticket en Resolved, dentro del SLA de 4 h |

## Causa raíz

Un ejecutable disfrazado de PDF pasó el filtro de correo y Windows ocultaba las extensiones de archivo, así que el usuario vio "factura_pdf" y no el `.exe`.

### 5 porqués

1. ¿Por qué se ejecutó malware? Porque el usuario abrió un adjunto que parecía una factura.
2. ¿Por qué parecía una factura? Porque Windows ocultaba la extensión `.exe` y el nombre decía "pdf".
3. ¿Por qué llegó el `.exe` al buzón? Porque el filtro de correo no bloqueaba adjuntos ejecutables.
4. ¿Por qué no se bloqueaban? Porque nunca se definió una política de tipos de adjunto permitidos.
5. ¿Por qué no existía esa política? Porque no había un responsable de revisar las reglas de seguridad del correo. **Causa raíz: falta de una política y un responsable de seguridad del correo.**

## Qué salió bien

- El usuario llamó de inmediato en vez de ignorar la alerta.
- La guía de contención (aislar sin apagar) se aplicó en 5 minutos.
- La escalada a Nivel 2 tuvo el motivo por escrito, así que Nivel 2 no tuvo que volver a preguntar.
- Todo quedó documentado en el ticket, con la hora de cada paso.

## Qué salió mal

- No existía un procedimiento escrito para incidentes de seguridad; Nivel 1 decidió los pasos sobre la marcha.
- El escaneo de los otros equipos dependió de que Nivel 2 estuviera disponible.

## Acciones

| Acción | Responsable | Prioridad | Fecha |
|---|---|---|---|
| Bloquear adjuntos `.exe`, `.js`, `.scr` y `.iso` en el filtro de correo | Nivel 2 | P0 | 13/11/2026 |
| GPO para mostrar extensiones de archivo en todos los equipos | Nivel 2 | P1 | 20/11/2026 |
| Procedimiento de 1 página para Nivel 1: aislar, no apagar, documentar, escalar | Nivel 1 | P1 | 20/11/2026 |
| Publicar el artículo kb08 (phishing) y enviarlo a todo el personal | Nivel 1 | P1 | 13/11/2026 |
| Capacitación de 15 minutos sobre facturas falsas | Gerencia | P2 | 30/11/2026 |

## Lecciones

Un antivirus actualizado detuvo este caso, pero la defensa no puede depender solo de él. Cerrar el camino de entrada (adjuntos ejecutables) y que la persona reporte rápido valen más que cualquier herramienta. La base sigue el ciclo de respuesta a incidentes de NIST SP 800-61 Rev. 3: detectar, contener, erradicar, recuperar y aprender.
