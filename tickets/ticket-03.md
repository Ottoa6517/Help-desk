# Ticket 3: Outlook no recibe correos

| Campo | Valor |
|---|---|
| Usuario | Carlos Méndez (Gerente de tienda), cuenta de dominio `DAILYBREW\cmendez` |
| Tema de ayuda | Correo y Microsoft 365 |
| Prioridad | High |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> Desde ayer no me llegan correos nuevos. Los proveedores dicen que me escribieron y yo no veo nada.

## Nota interna

```
SÍNTOMA: No entran correos nuevos desde ayer; los proveedores reciben rebotes.
DIAGNÓSTICO: La barra inferior de Outlook dice 'Buzón lleno'. En Archivo > Herramientas > Limpieza de buzón el tamaño es 49,8 de 50 GB. La carpeta Elementos eliminados ocupa 11 GB y hay correos con adjuntos de más de 20 MB.
CAUSA RAÍZ: Buzón al 100 % de su cuota: el servidor rechaza los correos entrantes.
SOLUCIÓN: Vacié Elementos eliminados, ordené por tamaño y moví los adjuntos grandes a OneDrive con el usuario. Quedó en 31 GB. Envié un correo de prueba desde otra cuenta: llegó en segundos.
PREVENCIÓN: Configurar autoarchivado y enseñar a compartir enlaces de OneDrive en vez de adjuntar archivos grandes.
TIEMPO: 30 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Carlos: tu buzón estaba lleno y por eso el servidor rechazaba los correos. Lo liberamos (ahora tienes 19 GB disponibles) y ya entran normal. Pide a los proveedores que reenvíen lo de ayer.

## Artículo de base de conocimiento

[kb02-outlook-no-recibe-correos.md](../base-de-conocimiento/kb02-outlook-no-recibe-correos.md)
