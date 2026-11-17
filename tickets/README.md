# Los 20 tickets

Escenarios ficticios de soporte de TI para Daily Brew. Cada ticket tiene su archivo con la nota interna completa (síntoma, diagnóstico, causa raíz, solución, prevención y tiempo) y la respuesta al usuario.

| # | Usuario | Tema | Prioridad | SLA | Canal | Asunto | Escalado | Artículo KB |
|---|---|---|---|---|---|---|---|---|
| 1 | María García | Red e internet | Emergency | Crítico - 4h | Teléfono | [No hay internet en caja](ticket-01.md) | Nivel 2 | - |
| 2 | Luis Cifuentes | Cuentas y accesos | Normal | Normal - 24h | En persona | [Olvidé mi contraseña](ticket-02.md) | - | kb01 |
| 3 | Carlos Méndez | Correo y Microsoft 365 | High | Normal - 24h | Portal web | [Outlook no recibe correos](ticket-03.md) | - | kb02 |
| 4 | Andrés Soto | Hardware | Normal | Normal - 24h | Portal web | [La impresora no imprime](ticket-04.md) | - | kb03 |
| 5 | Sofía Pérez | Software | Low | Baja - 72h | Portal web | [Necesito Zoom instalado](ticket-05.md) | - | - |
| 6 | Paola Recinos | Hardware | Normal | Normal - 24h | Portal web | [Laptop muy lenta](ticket-06.md) | - | kb04 |
| 7 | José Hernández | Cuentas y accesos | High | Normal - 24h | Teléfono | [Cuenta bloqueada](ticket-07.md) | - | - |
| 8 | Elena Ruiz | Correo y Microsoft 365 | Normal | Normal - 24h | Portal web | [No puedo abrir un archivo de OneDrive](ticket-08.md) | - | kb05 |
| 9 | María García | Hardware | High | Normal - 24h | En persona | [La pantalla de la caja no enciende](ticket-09.md) | - | - |
| 10 | Carlos Méndez | Red e internet | Normal | Normal - 24h | Portal web | [El Wi-Fi de la oficina es lento](ticket-10.md) | - | kb06 |
| 11 | Andrés Soto | Software | Normal | Normal - 24h | Portal web | [Excel se cierra solo](ticket-11.md) | - | kb07 |
| 12 | Luis Cifuentes | Seguridad | High | Crítico - 4h | Correo | [Me llegó un correo raro pidiendo mi clave](ticket-12.md) | Nivel 2 | kb08 |
| 13 | Paola Recinos | Correo y Microsoft 365 | Low | Baja - 72h | Portal web | [Cómo comparto una carpeta en Teams](ticket-13.md) | - | kb09 |
| 14 | Sofía Pérez | Hardware | Normal | Normal - 24h | En persona | [El celular de delivery no carga](ticket-14.md) | - | - |
| 15 | José Hernández | Cuentas y accesos | Normal | Normal - 24h | Portal web | [Necesito acceso a la carpeta de Inventario](ticket-15.md) | - | - |
| 16 | Elena Ruiz | Software | Normal | Normal - 24h | Portal web | [Windows pide reiniciar por actualizaciones](ticket-16.md) | - | - |
| 17 | Carlos Méndez | Seguridad | Emergency | Crítico - 4h | Teléfono | [El antivirus detectó una amenaza](ticket-17.md) | Nivel 2 | - |
| 18 | Andrés Soto | Red e internet | Normal | Normal - 24h | Portal web | [No me conecto a la VPN](ticket-18.md) | Nivel 2 | kb10 |
| 19 | María García | Hardware | Low | Baja - 72h | Portal web | [Necesito un segundo monitor](ticket-19.md) | - | - |
| 20 | Paola Recinos | Software | Normal | Normal - 24h | Portal web | [Usuario nuevo: preparar su computadora](ticket-20.md) | - | - |

Escalados a Nivel 2: 4 de 20. Los tickets 2, 7, 15 y 20 se pueden reproducir de verdad en el dominio del [laboratorio de Active Directory](https://github.com/Ottoa6517/lab-active-directory-dailybrew) con [`scripts/ad/tickets-en-el-dominio.ps1`](../scripts/ad/tickets-en-el-dominio.ps1).
