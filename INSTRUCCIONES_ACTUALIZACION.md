# Instrucciones para Actualizar la Base de Datos

## Cambios Realizados en la Sección de Beneficiarios

### 1. Nueva Columna en la Tabla `lecturas`

Se ha agregado una nueva columna `pagado` a la tabla `lecturas` para rastrear directamente si una lectura ha sido pagada o no.

### 2. Cómo Ejecutar el Script SQL

**Opción 1: Usando phpMyAdmin**
1. Abre phpMyAdmin en tu navegador: `http://localhost/phpmyadmin`
2. Selecciona la base de datos `agua` en el panel izquierdo
3. Haz clic en la pestaña "SQL" en la parte superior
4. Copia y pega el contenido del archivo `sql/agregar_columna_pagado_lecturas.sql`
5. Haz clic en el botón "Continuar" o "Go" para ejecutar el script

**Opción 2: Usando la línea de comandos de MySQL**
```bash
# Navega al directorio del proyecto
cd c:\xampp\htdocs\AGUA

# Ejecuta el script SQL
mysql -u root -p agua < sql/agregar_columna_pagado_lecturas.sql
```

### 3. Funcionalidades Mejoradas

#### Botón "Pagar"
- Ahora redirige correctamente a la sección de facturación
- Lleva el `id_lectura` como parámetro en la URL
- Solo se muestra si la lectura NO está en estado "Pagado"

#### Botón "Editar Estado"
- Permite cambiar entre los estados: **Pendiente**, **Pagado**, y **Cancelado**
- Incluye confirmación de doble paso para evitar cambios accidentales
- Muestra el estado actual y las opciones disponibles
- Actualiza automáticamente el modal después de guardar los cambios

### 4. Archivos Modificados

- `controladores/lecturas.php` - Actualizado para incluir la columna `pagado`
- `recursos/scripts/validacion_beneficiarios.js` - Mejorada la lógica de botones y modales
- `vistas/clientes.php` - Actualizada la versión del script
- `sql/agregar_columna_pagado_lecturas.sql` - Nuevo script para actualizar la BD

### 5. Verificación

Después de ejecutar el script SQL, verifica que:
1. La columna `pagado` existe en la tabla `lecturas`
2. Las lecturas con facturas pagadas tienen `pagado = 'SI'`
3. Las demás lecturas tienen `pagado = 'NO'`

Puedes verificar esto ejecutando:
```sql
SELECT id_lectura, id_usuario, fecha_lectura, pagado FROM lecturas LIMIT 10;
```

### 6. Notas Importantes

- **NO** se ha modificado ninguna otra funcionalidad existente
- Todos los cambios son compatibles con el código anterior
- La columna `pagado` se sincroniza automáticamente con el estado de las facturas
- El sistema sigue funcionando normalmente incluso si no ejecutas el script SQL (solo no verás la nueva funcionalidad)

---

**Fecha de actualización:** 2026-01-24  
**Versión:** 3462
