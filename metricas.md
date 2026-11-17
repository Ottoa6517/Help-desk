# Métricas del help desk

> **Datos simulados.** Los 20 tickets son ficticios y sus fechas se repartieron en dos semanas laborales con la fase `cronologia` del script, para que el dashboard tenga datos con sentido. Las cifras de esta página se calculan desde la base de datos de osTicket con `php scripts/osticket-lab.php metricas`.

## Resumen

| Indicador | Valor | Cómo se calcula |
|---|---|---|
| Tickets recibidos | 20 | |
| Tickets resueltos | 20 de 20 | Estado Resolved |
| Cumplimiento de SLA | 18 de 20 (90 %) | Resueltos antes del límite de su plan de SLA |
| Escalados a Nivel 2 | 4 (20 %) | Asignados al equipo Nivel 2 |
| Resueltos al primer contacto (FCR) | 15 (75 %) | Sin escalar y sin tener que pedir más datos al usuario |
| Tiempo medio de primera respuesta | 17 min | Del mensaje del usuario al acuse de recibo |
| Mediana de tiempo de resolución | 3,3 h | De la apertura al estado Resolved |
| Tiempo de trabajo del agente | 8,6 h en total | Suma del campo TIEMPO de las notas internas |

## Tickets por tema

| Tema | Tickets | % |
|---|---|---|
| Hardware | 5 | 25 % |
| Software | 4 | 20 % |
| Red e internet | 3 | 15 % |
| Cuentas y accesos | 3 | 15 % |
| Correo y Microsoft 365 | 3 | 15 % |
| Seguridad | 2 | 10 % |

## Tickets por canal

| Canal | Tickets |
|---|---|
| Portal web | 13 |
| Teléfono | 3 |
| En persona | 3 |
| Correo | 1 |

## Detalle

| # | Ticket | Asunto | Tema | Prioridad | SLA | Resolución | SLA cumplido | Escalado |
|---|---|---|---|---|---|---|---|---|
| 1 | 764345 | No hay internet en caja | Red e internet | emergency | Crítico - 4h | 2,7 h | Sí | Sí |
| 2 | 697421 | Olvidé mi contraseña | Cuentas y accesos | normal | Normal - 24h | 12 min | Sí | No |
| 3 | 893268 | Outlook no recibe correos | Correo y Microsoft 365 | high | Normal - 24h | 1,6 h | Sí | No |
| 4 | 682557 | La impresora no imprime | Hardware | normal | Normal - 24h | 40 min | Sí | No |
| 5 | 443025 | Necesito Zoom instalado | Software | low | Baja - 72h | 21,0 h | Sí | No |
| 6 | 301818 | Laptop muy lenta | Hardware | normal | Normal - 24h | 5,0 h | Sí | No |
| 7 | 342780 | Cuenta bloqueada | Cuentas y accesos | high | Normal - 24h | 18 min | Sí | No |
| 8 | 585385 | No puedo abrir un archivo de OneDrive | Correo y Microsoft 365 | normal | Normal - 24h | 1,2 h | Sí | No |
| 9 | 333185 | La pantalla de la caja no enciende | Hardware | high | Normal - 24h | 30 min | Sí | No |
| 10 | 356358 | El Wi-Fi de la oficina es lento | Red e internet | normal | Normal - 24h | 27,2 h | **No** | No |
| 11 | 611451 | Excel se cierra solo | Software | normal | Normal - 24h | 2,0 h | Sí | No |
| 12 | 795920 | Me llegó un correo raro pidiendo mi clave | Seguridad | high | Crítico - 4h | 2,5 h | Sí | Sí |
| 13 | 486585 | Cómo comparto una carpeta en Teams | Correo y Microsoft 365 | low | Baja - 72h | 3,3 h | Sí | No |
| 14 | 369735 | El celular de delivery no carga | Hardware | normal | Normal - 24h | 15 min | Sí | No |
| 15 | 413834 | Necesito acceso a la carpeta de Inventario | Cuentas y accesos | normal | Normal - 24h | 4,0 h | Sí | No |
| 16 | 320794 | Windows pide reiniciar por actualizaciones | Software | normal | Normal - 24h | 22,5 h | Sí | No |
| 17 | 637594 | El antivirus detectó una amenaza | Seguridad | emergency | Crítico - 4h | 3,5 h | Sí | Sí |
| 18 | 439295 | No me conecto a la VPN | Red e internet | normal | Normal - 24h | 31,0 h | **No** | Sí |
| 19 | 684330 | Necesito un segundo monitor | Hardware | low | Baja - 72h | 25,0 h | Sí | No |
| 20 | 395118 | Usuario nuevo: preparar su computadora | Software | normal | Normal - 24h | 7,0 h | Sí | No |

## Tres conclusiones

1. **Hardware** fue el tema con más tickets (5 de 20). **Cuentas y accesos** sumó 3 (15 %): contraseña olvidada, cuenta bloqueada y permisos. Propuesta: habilitar el autoservicio de restablecimiento de contraseña (SSPR) y publicar el artículo kb01 en la pantalla de inicio de sesión.
2. Se cumplió el SLA en 18 de 20 tickets (90 %). Los vencidos fueron el #10 (El Wi-Fi de la oficina es lento) y el #18 (No me conecto a la VPN): uno por un cambio programado fuera de horario sin pausar el SLA y otro por la cola de Nivel 2. Propuesta: usar el estado «en espera» o acordar la ventana con el usuario, y que Nivel 2 tenga una alerta al 75 % del SLA.
3. Se escalaron 4 tickets (20 %), todos de red o seguridad, que necesitaban permisos o contratos que Nivel 1 no tiene. Propuesta: un procedimiento escrito para incidentes de seguridad (aislar, documentar, escalar) para que Nivel 1 contenga el problema en los primeros 10 minutos.
