---
pregunta: ¿Qué hago si la impresora no imprime?
categoria: Hardware
audiencia: Usuario final (pasos 1 a 4) y agente (paso 5)
ticket_origen: 4
revision: 17/11/2026
---

**Para quién es:** cualquier empleado que manda a imprimir y no sale nada.

### Pasos para el usuario

1. Revisa la pantalla de la impresora: ¿tiene papel, tóner y ninguna luz de error? ¿Está encendida?
2. En tu computadora abre **Configuración** > **Bluetooth y dispositivos** > **Impresoras y escáneres** y elige la impresora. Revisa que sea la impresora correcta (no "Microsoft Print to PDF").
3. Haz clic en **Abrir cola de impresión**. Si hay documentos con estado **Error**, haz clic derecho > **Cancelar** en cada uno.
4. Apaga la impresora 30 segundos, enciéndela y vuelve a imprimir.

### Paso para el agente (requiere permisos de administrador)

5. Si la cola no se limpia: `services.msc` > **Print Spooler** > **Detener**; borrar el contenido de `C:\Windows\System32\spool\PRINTERS`; **Iniciar** el servicio y enviar una página de prueba.

### Cuándo pedir ayuda

- Si después del paso 4 sigue sin imprimir, abre un ticket en **Hardware** e indica el modelo de impresora y el mensaje de error.
- Si la impresora hace ruidos extraños o hay papel atascado que no puedes sacar con facilidad: no fuerces nada.
