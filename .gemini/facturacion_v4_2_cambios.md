# Mejoras Implementadas - Versión 4.2

## 🛠️ Correcciones y Persistencia
- **Persistencia de Tarifa**: Ahora el precio por m³ se guarda en la base de datos (tabla `configuracion`).
  - Al iniciar, carga el valor desde BD.
  - Al editar y guardar, actualiza el valor en BD para todos los usuarios.
- **Correcíón de Historial**: Al limpiar la búsqueda (botón X), el historial de facturas vuelve a mostrar la lista global de recientes, arreglando el problema de "desaparición" de datos.
- **Búsqueda Avanzada**: Ahora se muestra el mes de la próxima lectura pendiente directamente en los resultados de búsqueda (ej: `• Enero 2026`).

## 🧾 Ticket Personalizado
- **Nombre Corregido**: Se actualizó el encabezado del ticket a:
  > **SISTEMA POTABLE DE**
  > **SAN NICOLÁS ZECALCOAYAN**
- Se mantiene el formato térmico profesional.

## 💾 Cambios Técnicos en Base de Datos
- **Nueva Tabla**: `configuracion` (creada automáticamente si no existe).
- **Nuevos Endpoints**: `get_rate` y `update_rate`.
- **Query Optimizada**: `search_users` ahora hace una subconsulta inteligente para traer el mes pendiente más antiguo no pagado.

**Versión**: 4.2
**Fecha**: 2026-01-24
**Estado**: ✅ Funcional y Persistente
