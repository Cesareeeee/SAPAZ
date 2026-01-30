# INSTRUCCIONES PARA CREAR USUARIOS DEL SISTEMA (ACTUALIZADO)

## Usuarios a crear:

### LECTURISTAS (Contraseña: lecturista123)
1. Daniel Arellano Roldán - Usuario: daniel.arellano
2. Antolín Escalante Rojas - Usuario: antolin.escalante
3. Gaudencio Gutierrez Palacios - Usuario: gaudencio.gutierrez
4. Crisoforo Gutierrez Pérez - Usuario: crisoforo.gutierrez
5. Rodrigo Pérez Pérez - Usuario: rodrigo.perez
6. Edmundo Reyes Pérez - Usuario: edmundo.reyes

### ADMINISTRADOR (Contraseña: admin123)
7. Lucio Pérez - Usuario: lucio.perez

## Pasos para ejecutar:

1. Abrir phpMyAdmin en: http://localhost/phpmyadmin
2. Seleccionar la base de datos "agua"
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido del archivo: sql/crear_usuarios_lecturistas.sql
5. Hacer clic en "Continuar" para ejecutar el script

## Permisos por rol:

### LECTURISTA - Puede acceder a:
- ✅ Agregar Nueva Lectura
- ✅ Historial de Lecturas
- ✅ Lista de Beneficiarios
- ✅ **Agregar Beneficiario** (ACTUALIZADO)

### ADMINISTRADOR - Acceso completo a:
- ✅ Agregar Nueva Lectura
- ✅ Historial de Lecturas
- ✅ Lista de Beneficiarios
- ✅ Agregar Beneficiario
- ✅ Dashboard
- ✅ Facturación
- ✅ Reportes
- ✅ Configuración

## Notas importantes:

### ✅ CORRECCIONES APLICADAS:
- **Contraseñas corregidas**: Los hashes ahora son válidos y funcionan correctamente
- **Permisos actualizados**: Los lecturistas ahora pueden agregar beneficiarios
- **Hashes verificados**: Cada contraseña fue probada antes de incluirse en el script

### 🔐 SEGURIDAD:
- Las contraseñas están hasheadas con bcrypt (cost 12) para máxima seguridad
- Los hashes fueron generados dinámicamente usando PHP
- Todos los usuarios están activos por defecto
- Los nombres de usuario son únicos en el sistema

### 📝 VERIFICACIÓN:
Después de ejecutar el script, verifica que:
1. Se crearon 7 usuarios en total
2. 6 usuarios tienen rol LECTURISTA
3. 1 usuario tiene rol ADMIN
4. Todos los usuarios están activos (activo = 1)

### 🧪 PRUEBAS:
Para probar que las contraseñas funcionan:
1. Intenta iniciar sesión con: `daniel.arellano` / `lecturista123`
2. Intenta iniciar sesión con: `lucio.perez` / `admin123`
3. Verifica que cada usuario ve las opciones correctas en el menú

## Solución de problemas:

### Si las contraseñas no funcionan:
1. Verifica que ejecutaste el script SQL más reciente
2. Asegúrate de que la tabla `usuarios_sistema` existe
3. Verifica que la columna `contrasena` acepta VARCHAR(255)
4. Revisa que no haya espacios extra en usuario o contraseña

### Si un usuario no puede agregar beneficiarios:
1. Verifica que el usuario tiene sesión activa
2. Revisa que `$_SESSION['rol']` está definido
3. Asegúrate de que la página `clientes.php` es accesible

## Archivo de hash de contraseñas:

Si necesitas generar nuevos hashes, ejecuta:
```bash
php generar_hash.php
```

Este script generará hashes válidos para las contraseñas y los verificará automáticamente.
