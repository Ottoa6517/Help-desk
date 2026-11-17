# Ticket 5: Necesito Zoom instalado

| Campo | Valor |
|---|---|
| Usuario | Sofía Pérez (Repartidora (delivery)), cuenta de dominio `DAILYBREW\sperez` |
| Tema de ayuda | Software |
| Prioridad | Low |
| Plan de SLA | Baja - 72h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> Me pidieron entrar a una capacitación por Zoom el jueves y no lo tengo en la computadora.

## Nota interna

```
SÍNTOMA: Solicitud de servicio: instalar Zoom para una capacitación.
DIAGNÓSTICO: Confirmé por correo con Carlos (gerente) que la capacitación es oficial y que Zoom está en la lista de software aprobado. El usuario no es administrador del equipo, así que no puede instalarlo solo (correcto).
CAUSA RAÍZ: Programa no instalado; es una solicitud, no una falla.
SOLUCIÓN: Instalé desde la fuente oficial con winget install --id Zoom.Zoom -e y comprobé la firma digital del instalador (Zoom Communications). Hice una reunión de prueba con audio y cámara: OK.
PREVENCIÓN: Publicar la lista de software aprobado en el portal para que las solicitudes lleguen con la aprobación incluida.
TIEMPO: 15 min | ESCALADO: No
```

## Respuesta al usuario

> Hola Sofía: ya tienes Zoom instalado y probado con audio y cámara. Para la capacitación solo abre el enlace que te enviaron.
