# Seguridad del laboratorio

XAMPP está hecho para desarrollo, no para producción: trae MariaDB con root sin contraseña y servicios que escuchan en toda la red. Este laboratorio corre solo en mi PC, pero lo dejé asegurado como si fuera a usarse de verdad, porque eso es lo que se espera de alguien de soporte.

## Lista de verificación

| Capa | Control | Dónde se aplica | Por qué |
|---|---|---|---|
| Software | osTicket **1.18.4**, la última versión | Script 03 | Las versiones anteriores a 1.18.3 tienen CVE-2026-22200 (lectura de archivos del servidor) y anteriores a 1.18.2 tienen CVE-2025-26241 (inyección SQL) y CVE-2025-45387 (control de acceso) |
| Software | PHP **8.4** (XAMPP 8.4.27), no 8.5 ni 8.2 | Script 01 | 8.5 no está soportado por osTicket; 8.2 sale de soporte de seguridad el 31/12/2026 |
| Cadena de suministro | SHA-256 del ZIP fijado en el script y verificado antes de instalar | Script 03 | Si el archivo cambia, no se instala. Comparé el contenido del ZIP con la etiqueta v1.18.4 del repositorio oficial: solo difiere en marcas de compilación |
| Red | Apache escucha en `127.0.0.1:80` y MariaDB en `127.0.0.1` | Script 02 | Nadie de la red Wi-Fi puede abrir el help desk ni la base de datos |
| Red | phpMyAdmin solo desde el propio equipo (`Require local`, viene así en XAMPP) | Verificación | Es la puerta más atacada de XAMPP |
| Base de datos | Root con contraseña en `localhost`, `127.0.0.1` y `::1`; usuario anónimo y base `test` eliminados | Script 02 | XAMPP los trae abiertos |
| Base de datos | `osticket_user` con permisos **solo** sobre `osticket_db` | Script 02 | Mínimo privilegio: si se filtra esa contraseña, no se ven otras bases |
| PHP | `expose_php=Off`, `display_errors=Off`, `log_errors=On` | Script 02 | No revelar versión ni rutas internas en los errores (hoja OWASP de configuración PHP) |
| PHP | Cookies de sesión `HttpOnly`, `use_strict_mode`, `SameSite=Lax` | Script 02 | Reduce robo de sesión y CSRF |
| Apache | `ServerTokens Prod`, `ServerSignature Off` | Script 02 | No anunciar la versión de Apache |
| osTicket | Carpeta `setup` borrada y `ost-config.php` en solo lectura | Script 03 | El propio osTicket lo exige |
| osTicket | Registro de usuarios **Private** (nadie se registra solo) | Fase configurar | Mitigación recomendada para CVE-2026-22200 |
| osTicket | CAPTCHA en el formulario de tickets para invitados | Fase configurar | Evita spam de tickets |
| osTicket | Agente de Nivel 2 con rol **Expanded Access**, no administrador | Fase configurar | Mínimo privilegio |
| Contraseñas | Las pide cada script en pantalla; ninguna queda en archivos ni en el repositorio | Todos | Los respaldos de credenciales temporales se borran al terminar |
| Contraseñas | Política basada en longitud (frase de 15+ caracteres), sin cambios periódicos forzados | Artículo kb01, respuesta 04 | NIST SP 800-63B-4 (2025): la longitud pesa más que la complejidad y el cambio forzado empeora la seguridad |
| Repositorio | `.gitignore` excluye `ost-config.php`, respaldos y `.env` | `.gitignore` | Nunca subir credenciales |
| Repositorio | Activar **Secret scanning** y **Push protection** en GitHub | Configuración del repo | Bloquea un `git push` que contenga una clave |
| Respaldo | Volcado diario de la base con `mysqldump`, se conservan 7 | Script 04 | Sin respaldo no hay recuperación |

## Qué haría distinto en producción

Esto no se puede hacer en un laboratorio local sin dominio ni servidor de correo, pero conviene saber decirlo en una entrevista:

1. **No usar XAMPP.** Un servidor Linux (Ubuntu Server + Apache o Nginx + PHP-FPM + MariaDB) o IIS en Windows Server, con actualizaciones automáticas.
2. **HTTPS obligatorio** con un certificado válido y la opción *Force HTTPS* de osTicket; con eso se puede activar `session.cookie_secure`.
3. **Doble factor para agentes** (plugin de 2FA de osTicket) y que la cuenta de administrador no se use para trabajar tickets.
4. **Correo con OAuth2** (Microsoft 365 o Google ya no aceptan usuario y contraseña simples).
5. **Inicio de sesión con Active Directory** (plugin LDAP) para no tener contraseñas separadas.
6. **Firewall** que solo deje entrar al portal desde la red de la empresa o por VPN.
7. **Suscribirse a los avisos de seguridad** de osTicket y actualizar dentro de la semana de cada versión.
8. **Respaldos fuera del equipo** (otro disco o la nube) y una prueba de restauración cada mes.

## Si se filtra una contraseña

1. Cambiarla de inmediato (root, `osticket_user` o la del agente).
2. Si fue `osticket_user`: actualizar `include\ost-config.php` (quitar solo lectura, editar, volver a ponerlo).
3. Revisar Admin Panel > Dashboard > System Logs y los inicios de sesión recientes.
4. Si la contraseña llegó a GitHub: cambiarla **primero**; borrarla del historial no basta, porque pudo copiarse.
