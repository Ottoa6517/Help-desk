# Mantenimiento: respaldo, restauración y actualización

## Respaldo

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\windows\04-respaldo.ps1 -Conservar 7
```

Genera `respaldos\osticket-AAAAMMDD-HHMMSS.sql.zip`. La carpeta está en `.gitignore`.

Respaldar también, una vez, `C:\xampp\htdocs\osticket\include\ost-config.php` (tiene la clave interna `SECRET_SALT`; sin ella no se pueden leer algunos datos cifrados de la base). Guárdalo fuera del repositorio.

## Restaurar (probarlo al menos una vez)

```powershell
Expand-Archive .\respaldos\osticket-20261117-090000.sql.zip -DestinationPath $env:TEMP\rest
C:\xampp\mysql\bin\mysql.exe -u root -p -e "DROP DATABASE osticket_db; CREATE DATABASE osticket_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
# Con cmd para que PowerShell no cambie la codificación del archivo al redirigirlo
cmd /c "C:\xampp\mysql\bin\mysql.exe -u root -p osticket_db < %TEMP%\rest\osticket-20261117-090000.sql"
```

Un respaldo que nunca se restauró no es un respaldo comprobado.

## Tareas periódicas (cron)

osTicket revisa vencimientos de SLA y tareas internas cuando corre su cron. El laboratorio usa **Use agent activity as cron** (se ejecuta cuando un agente navega por el panel). En un entorno real se programa cada 5 minutos:

```powershell
schtasks /Create /TN "osTicket cron" /SC MINUTE /MO 5 /RU SYSTEM `
  /TR "C:\xampp\php\php.exe C:\xampp\htdocs\osticket\api\cron.php"
```

## Actualizar osTicket

1. Respaldo de la base y de `ost-config.php`.
2. Descargar la versión nueva y verificar su SHA-256 contra la página oficial de la versión.
3. Reemplazar los archivos excepto `include\ost-config.php`.
4. Entrar a `/scp`: si hay cambios de base de datos, osTicket pide correr el actualizador.
5. Borrar la carpeta `setup` de nuevo y comprobar un ticket de prueba.
