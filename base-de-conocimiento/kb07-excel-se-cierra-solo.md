---
pregunta: ¿Qué hago si Excel (u otro programa de Office) se cierra solo?
categoria: Software
audiencia: Usuario final (pasos 1 a 3) y agente (pasos 4 y 5)
ticket_origen: 11
revision: 17/11/2026
---

**Para quién es:** usuarios a quienes Excel, Word o Outlook se les cierra sin mensaje.

### Pasos para el usuario

1. Guarda tu trabajo en otros programas y reinicia la computadora.
2. Abre Excel en **modo seguro**: tecla **Windows + R**, escribe `excel /safe` y pulsa Enter.
3. Si en modo seguro **sí** funciona, el problema es un complemento. Anota qué complementos instalaste recientemente y abre un ticket en **Software**.

### Pasos para el agente

4. **Archivo** > **Opciones** > **Complementos** > en **Administrar** elegir **Complementos COM** > **Ir** > desmarcar los complementos de terceros uno por uno hasta encontrar el que falla.
5. Si no es un complemento: **Configuración** > **Aplicaciones** > **Aplicaciones instaladas** > Microsoft 365 > **Modificar** > **Reparación rápida**. Si no basta, **Reparación en línea**. Revisar en el Visor de eventos (registro Aplicación) el evento 1000 para identificar el módulo que falla.

### Cuándo pedir ayuda

- Si se cierra también en modo seguro.
- Si el archivo que intentas abrir es el único que lo cierra: puede estar dañado. Adjunta una copia al ticket.
