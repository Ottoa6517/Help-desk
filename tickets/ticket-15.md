# Ticket 15: Necesito acceso a la carpeta de Inventario

| Campo | Valor |
|---|---|
| Usuario | José Hernández (Bodega), cuenta de dominio `DAILYBREW\jhernandez` |
| Tema de ayuda | Cuentas y accesos |
| Prioridad | Normal |
| Plan de SLA | Normal - 24h |
| Canal | Portal web |
| Escalado | No |

## Lo que escribió el usuario

> Ahora llevo el control de bodega y necesito abrir la carpeta de Inventario en la unidad S:.

## Nota interna

```
SÍNTOMA: José no puede abrir S:\Inventario (Acceso denegado).
DIAGNÓSTICO: La carpeta \\dc01\Compartido\Inventario da permisos al grupo GG_Inventario y José no es miembro. Pedí aprobación por correo a Carlos (gerente): aprobado y adjunto al ticket.
CAUSA RAÍZ: Solicitud de acceso nueva; el permiso no existía.
SOLUCIÓN: En DC01: Add-ADGroupMember GG_Inventario -Members jhernandez. Le pedí cerrar y abrir sesión para que se actualice su token de grupos. Abrió la carpeta y creó un archivo de prueba: OK.
PREVENCIÓN: Dar permisos siempre por grupo, nunca a usuarios sueltos, y solo con aprobación del responsable de la carpeta (mínimo privilegio).
TIEMPO: 20 min | ESCALADO: No
```

## Respuesta al usuario

> Hola José: ya tienes acceso a la carpeta de Inventario con la aprobación de Carlos. Si no la ves, cierra sesión y vuelve a entrar.
