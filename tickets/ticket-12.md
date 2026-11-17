# Ticket 12: Me llegó un correo raro pidiendo mi clave

| Campo | Valor |
|---|---|
| Usuario | Luis Cifuentes (Barista), cuenta de dominio `DAILYBREW\lcifuentes` |
| Tema de ayuda | Seguridad |
| Prioridad | High |
| Plan de SLA | Crítico - 4h |
| Canal | Correo |
| Escalado | Sí, a Nivel 2 |

## Lo que escribió el usuario

> Me llegó un correo de 'Microsoft' que dice que mi cuenta se va a cerrar si no confirmo mi contraseña en un enlace. No le di clic.

## Nota interna

```
SÍNTOMA: Correo que suplanta a Microsoft y pide confirmar la contraseña en un enlace.
DIAGNÓSTICO: Remitente seguridad@micros0ft-verify.com (cero en vez de o). Encabezados: SPF fail. El enlace lleva a una página falsa de inicio de sesión. Luis confirma que no hizo clic ni escribió su clave. Otros 4 empleados recibieron el mismo correo.
CAUSA RAÍZ: Campaña de phishing dirigida a la empresa.
SOLUCIÓN: Luis lo reportó con el botón Reportar phishing y bloqueó al remitente. Escalé a Nivel 2: eliminó el correo de los 5 buzones y bloqueó el dominio. Envié un aviso a todo el personal con una captura del correo falso. Como nadie escribió su clave, no fue necesario restablecer contraseñas.
PREVENCIÓN: Habilitar MFA para todos y capacitación corta sobre phishing. Si alguien hubiera escrito su clave: restablecerla, cerrar sus sesiones y revisar reglas del buzón.
TIEMPO: 30 min | ESCALADO: Sí
```

## Por qué se escaló

Hay que buscar y eliminar el correo de todos los buzones y bloquear el dominio del remitente para toda la empresa; eso requiere permisos de administración de Exchange.

## Respuesta al usuario

> Hola Luis: hiciste lo correcto al no dar clic y reportarlo. Era un correo falso (phishing) y lo eliminamos de todos los buzones de la empresa. Gracias a tu aviso alertamos a todo el equipo.

## Artículo de base de conocimiento

[kb08-reconocer-y-reportar-phishing.md](../base-de-conocimiento/kb08-reconocer-y-reportar-phishing.md)
