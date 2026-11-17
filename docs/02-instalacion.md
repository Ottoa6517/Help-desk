# Instalación paso a paso (Windows + XAMPP)

Esta es la guía original del proyecto, corregida con lo que encontré al probarla de punta a punta contra osTicket 1.18.4. Las diferencias están marcadas con ⚠️.

Tiempo: 1 a 2 horas la primera vez; unos 15 minutos con los scripts.

## Qué cambia respecto a la guía original

| # | La guía decía | Qué pasa en realidad | Qué hacer |
|---|---|---|---|
| 1 | "Descarga XAMPP con PHP 8.2 o superior" | Desde el 23/10/2026 la primera opción de apachefriends.org es **XAMPP 8.5.12 (PHP 8.5)**. osTicket 1.18.4 solo admite **PHP 8.2 a 8.4**. | Descarga **XAMPP 8.4.27** (segunda opción de la lista). |
| 2 | XAMPP 8.2 como opción válida | PHP 8.2 deja de recibir parches de seguridad el 31/12/2026. | Evítalo en instalaciones nuevas. |
| 3 | "La versión actual es la 1.18" | La última es **1.18.4** (17/06/2026). Las anteriores a 1.18.3 tienen fallos graves publicados (CVE-2026-22200: lectura de archivos del servidor; CVE-2025-26241: inyección SQL). | Instala 1.18.4 y verifica el SHA-256 del ZIP. |
| 4 | Crear el agente "Ana López" con correo `@dailybrew.local` | osTicket rechaza el correo ("Valid email is required") porque verifica que el dominio exista en DNS. | Admin Panel > Settings > Emails: desmarca **Verify Email Addresses** antes de crear agentes. |
| 5 | "Ana López" como agente de Nivel 2 | En el laboratorio de AD ya existe `alopez` como usuaria de Ventas. | El agente de Nivel 2 se llama **Andrea Solís**. |
| 6 | No menciona activar la base de conocimiento | Sin activarla, los artículos no aparecen en el portal aunque estén publicados. | Admin Panel > Settings > Knowledgebase > **Enable Knowledge Base**. |
| 7 | Formato de fecha día/mes/año | El formato solo se aplica si "Date and Time Format" está en **Custom**. | Elige Custom y pon `dd/MM/y`. |
| 8 | "Transfiérelo al equipo Nivel 2" | En osTicket los equipos se **asignan** (Assign); "Transfer" es solo entre departamentos. | Usa **Assign > Team > Nivel 2**. |
| 9 | Registro de usuarios abierto (valor por defecto: Public) | Cualquiera puede crearse una cuenta; es una de las mitigaciones que pide el aviso de CVE-2026-22200. | Admin Panel > Settings > Users > Registration Method: **Private**. |
| 10 | Root de MariaDB sin contraseña (como viene XAMPP) | phpMyAdmin entra como root sin pedir nada. | Pon contraseña a root (script 02). |
| 11 | No menciona respaldos | Si se daña el disco se pierde todo el historial. | `scripts/windows/04-respaldo.ps1`. |
| 12 | No menciona el cron | osTicket marca tickets vencidos solo cuando corre su tarea programada. | Admin Panel > Settings > System: **Use agent activity as cron** (o una tarea programada). |

## Opción A: con los scripts (recomendada)

Abre PowerShell **como administrador** en la carpeta del repositorio.

```powershell
# 0. Instala XAMPP 8.4.27 en C:\xampp (desmarca Perl, Tomcat, Mercury y FileZilla)
# 1. Revisión previa (solo lee)
powershell -ExecutionPolicy Bypass -File .\scripts\windows\01-verificar-entorno.ps1

# 2. Con Apache y MySQL detenidos: extensiones, php.ini seguro, solo localhost, root con contraseña, base de datos
powershell -ExecutionPolicy Bypass -File .\scripts\windows\02-preparar-xampp.ps1 -WhatIf   # primero simula
powershell -ExecutionPolicy Bypass -File .\scripts\windows\02-preparar-xampp.ps1

# 3. Enciende Apache y MySQL en el XAMPP Control Panel. Descarga, verifica, instala y asegura osTicket
powershell -ExecutionPolicy Bypass -File .\scripts\windows\03-instalar-osticket.ps1 -Nombre Otto -Apellido "Aragón" -Correo otto.admin@dailybrew.local

# 4. Configuración, usuarios, tickets, resolución, base de conocimiento, cronología y métricas
powershell -ExecutionPolicy Bypass -File .\scripts\windows\05-cargar-laboratorio.ps1 -Fase todo
```

`-ExecutionPolicy Bypass` aplica solo a ese comando; no cambia la política del equipo.

Si prefieres ir fase por fase para tomar capturas entre una y otra: `-Fase configurar`, `usuarios`, `tickets`, `resolver`, `kb`, `cronologia`, `metricas`.

## Opción B: a mano (lo que pide la guía)

### 1.1 XAMPP
1. En apachefriends.org descarga **XAMPP for Windows 8.4.27** (no la 8.5.12).
2. Instálalo en `C:\xampp`. Desmarca Perl, Tomcat, Mercury y FileZilla.
3. XAMPP Control Panel > **Start** en Apache y MySQL. Prueba `http://localhost`.

### 1.2 Extensiones de PHP
Config > PHP (php.ini). Quita el `;` del inicio de `extension=intl`, `extension=gd`, `extension=mysqli`, `extension=mbstring`. Mientras estás ahí, cambia también `expose_php = Off` y `display_errors = Off`. Guarda y reinicia Apache.

### 1.3 Base de datos
1. ⚠️ Primero ponle contraseña a root: phpMyAdmin > Cuentas de usuario > root (localhost) > Editar privilegios > Cambiar contraseña. Después, en `C:\xampp\phpMyAdmin\config.inc.php`, cambia `'auth_type'` de `'config'` a `'cookie'` para que phpMyAdmin te pida usuario y contraseña.
2. Bases de datos > `osticket_db` con cotejamiento `utf8mb4_general_ci`.
3. Cuentas de usuario > Agregar: `osticket_user`, servidor `localhost`, contraseña larga. Privilegios **solo** sobre `osticket_db`.

### 1.4 osTicket
1. Descarga `osTicket-v1.18.4.zip` desde osticket.com o desde la página de versiones de GitHub.
2. ⚠️ Verifica la integridad antes de descomprimir:
   ```powershell
   Get-FileHash .\osTicket-v1.18.4.zip -Algorithm SHA256
   # Debe dar A4187DF5F52DD41625E6B61B322245853542E12B5969A45F0A6D81F7AB8151A7
   ```
3. Copia el **contenido** de `upload` a `C:\xampp\htdocs\osticket`.
4. En `include`, copia `ost-sampleconfig.php` como `ost-config.php`.

### 1.5 Instalador
`http://localhost/osticket/setup`: todo lo "Required" en verde (APCu e IMAP aparecen en rojo, pero son opcionales). Llena el formulario con los datos de la guía y **Install Now**. Toma la captura del mensaje de felicitación.

### 1.6 Asegurar
1. Borra `C:\xampp\htdocs\osticket\setup`.
2. `include\ost-config.php` > Propiedades > Solo lectura.
3. Portal: `http://localhost/osticket` · Agentes: `http://localhost/osticket/scp`.

## Problemas comunes

| Problema | Solución |
|---|---|
| Apache no arranca (puerto 80 ocupado) | `Get-NetTCPConnection -LocalPort 80` muestra qué programa lo usa. Detén IIS (`iisreset /stop`) o cambia `Listen 127.0.0.1:80` por `Listen 127.0.0.1:8080` en httpd.conf y usa `http://localhost:8080/osticket`. |
| MySQL no arranca | Otro MySQL usa el 3306: detenlo en `services.msc`. |
| El instalador marca PHP en rojo | XAMPP trae PHP menor a 8.2: instala XAMPP 8.4.27. |
| osTicket da errores raros o pantallas en blanco | ¿Instalaste XAMPP 8.5? osTicket 1.18.4 no está probado con PHP 8.5. |
| "Configuration file missing" | No creaste `ost-config.php` (paso 1.4). |
| "Configuration file not writable" | Quita el Solo lectura, instala y vuelve a ponerlo. |
| "Valid email is required" al crear un agente | Desactiva **Verify Email Addresses** (Admin Panel > Settings > Emails). |
| La base de conocimiento no aparece en el portal | Activa **Enable Knowledge Base** y marca las categorías como públicas. |
| phpMyAdmin: "Access denied for user root" | Cambiaste la contraseña de root: pon `auth_type` en `cookie` en config.inc.php. |
| Tildes raras al correr los scripts .ps1 | Los scripts están en UTF-8 con BOM; no los guardes con el Bloc de notas en otra codificación. |
