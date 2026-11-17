# Ticket 8: No puedo abrir un archivo de OneDrive

| Campo | Valor |
|---|---|
| Usuario | Elena Ruiz (Atención al cliente), cuenta de dominio `DAILYBREW\eruiz` |
| Tema de ayuda | Correo y Microsoft 365 |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> Paola me mandó el enlace del calendario de promociones y me sale que no tengo acceso.

## Nota interna

```
SÍNTOMA: Al abrir el enlace aparece 'Necesita permiso para obtener acceso a este elemento'.
DIAGNÓSTICO: Elena estaba abriendo el enlace con su cuenta personal de Microsoft en el navegador. Además el archivo estaba compartido solo con 'Personas específicas' y ella no estaba en la lista.
CAUSA RAÍZ: Permisos: el archivo no estaba compartido con la cuenta de trabajo de Elena.
SOLUCIÓN: Pedí a Paola (propietaria) que compartiera el archivo con eruiz@dailybrew.local con permiso de edición. Elena cerró sesión de la cuenta personal e ingresó con la del trabajo: abrió el archivo.
PREVENCIÓN: Usar un perfil de navegador solo para la cuenta del trabajo.
TIEMPO: 10 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Elena: el archivo no estaba compartido con tu cuenta del trabajo y además el navegador usaba tu cuenta personal. Paola ya te dio acceso de edición. Si te vuelve a pasar, revisa con qué cuenta entraste.

## Artículo de base de conocimiento

[kb05-no-puedo-abrir-archivo-onedrive.md](../base-de-conocimiento/kb05-no-puedo-abrir-archivo-onedrive.md)
