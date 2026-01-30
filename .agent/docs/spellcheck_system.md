# Sistema de Corrección Ortográfica - Documentación

## 📝 Descripción General

Se ha implementado un sistema completo de corrección ortográfica para los campos de nombre en el formulario de beneficiarios. Este sistema incluye:

1. **Detección automática de errores** mediante el corrector del navegador
2. **Menú contextual personalizado** con click derecho
3. **Diccionario de nombres y apellidos mexicanos** con acentuación correcta
4. **Sugerencias inteligentes** de corrección

## 🎯 Características Principales

### 1. Corrección Automática del Navegador
- Los campos de nombre tienen habilitado `spellcheck="true"`
- El navegador subraya automáticamente las palabras con errores ortográficos
- Configurado para español (`lang="es"`)

### 2. Menú Contextual Personalizado
- **Activación**: Click derecho sobre cualquier palabra en el campo de nombre
- **Funcionalidad**: 
  - Muestra la palabra seleccionada
  - Ofrece sugerencias de corrección
  - Permite aplicar la corrección con un solo click
  - Opción de ignorar la sugerencia

### 3. Diccionario Integrado
El sistema incluye más de 80 nombres y apellidos mexicanos comunes con su acentuación correcta:

**Nombres masculinos**: José, Jesús, Martín, Ramón, Ángel, Andrés, Óscar, Víctor, Héctor, Rubén, Adrián, Sebastián, Fabián, Julián, Agustín, etc.

**Nombres femeninos**: María, Inés, Mónica, Verónica, Sofía, Lucía, Rocío, Belén, etc.

**Apellidos**: García, Gómez, López, Martínez, Rodríguez, Hernández, González, Pérez, Sánchez, Ramírez, Díaz, Jiménez, Álvarez, Gutiérrez, Vázquez, etc.

### 4. Sugerencias Inteligentes
El sistema ofrece tres tipos de sugerencias:

1. **Corrección desde diccionario**: Si la palabra está en el diccionario, sugiere la versión correcta con acentos
2. **Capitalización**: Sugiere la primera letra en mayúscula
3. **Mayúsculas completas**: Opción para nombres en mayúsculas

## 🚀 Cómo Usar

### Para el Usuario Final:

1. **Escribir el nombre** en el campo correspondiente
2. Si hay un error ortográfico, el navegador lo subrayará en rojo
3. **Click derecho** sobre la palabra con error
4. Se abrirá un menú con sugerencias
5. **Click en la sugerencia** deseada para aplicar la corrección
6. La palabra se reemplaza automáticamente

### Ejemplo de Uso:

```
Usuario escribe: "jose garcia"
Click derecho en "jose" → Sugerencia: "José"
Click derecho en "garcia" → Sugerencia: "García"
Resultado: "José García"
```

## 🎨 Diseño del Menú Contextual

- **Estilo moderno**: Bordes redondeados, sombras suaves
- **Interactivo**: Hover effects en las opciones
- **Responsive**: Se posiciona donde haces click
- **Accesible**: Se puede cerrar con Escape o click fuera

## 🔧 Campos Afectados

El sistema de corrección ortográfica está activo en:

1. ✅ Campo "Nombre Completo" en el formulario de **Agregar Beneficiario**
2. ✅ Campo "Nombre Completo" en el formulario de **Editar Beneficiario**

## 📋 Funcionalidades Adicionales

### Atajos de Teclado:
- **Escape**: Cerrar el menú contextual
- **Click fuera**: Cerrar el menú automáticamente

### Comportamiento Inteligente:
- El menú se oculta automáticamente al hacer scroll
- Se oculta al hacer click en el campo
- Mantiene el foco en el campo después de corregir
- Dispara eventos de validación después de corregir

## 🔍 Detalles Técnicos

### Archivos Modificados:
1. `vistas/clientes.php` - Agregados atributos spellcheck y lang
2. `recursos/scripts/spellcheck.js` - Sistema de corrección completo

### Tecnologías Utilizadas:
- JavaScript vanilla (sin dependencias)
- API de selección de texto del navegador
- Eventos contextuales (contextmenu)
- Manipulación del DOM

### Compatibilidad:
- ✅ Chrome/Edge
- ✅ Firefox
- ✅ Safari
- ✅ Opera

## 📊 Ventajas del Sistema

1. **Mejora la calidad de los datos**: Nombres correctamente escritos con acentos
2. **Fácil de usar**: Interfaz intuitiva con click derecho
3. **No invasivo**: Solo aparece cuando se necesita
4. **Personalizable**: Fácil agregar más palabras al diccionario
5. **Sin dependencias externas**: No requiere librerías adicionales

## 🔄 Mantenimiento

### Para Agregar Nuevas Palabras al Diccionario:

Editar el archivo `recursos/scripts/spellcheck.js` y agregar entradas al objeto `correccionesComunes`:

```javascript
const correccionesComunes = {
    // ... palabras existentes ...
    'nuevapalabra': 'NuevaPalabra',
    'otrapalabra': 'OtraPalabra'
};
```

### Para Modificar el Estilo del Menú:

Editar los estilos CSS inline en la función `mostrarMenuContextual()` del archivo `spellcheck.js`.

## 🎯 Casos de Uso Comunes

1. **Nombres sin acentos**: jose → José, maria → María
2. **Apellidos sin acentos**: garcia → García, lopez → López
3. **Capitalización incorrecta**: JUAN PÉREZ → Juan Pérez
4. **Mezcla de casos**: jOsE gArCiA → José García

## ⚠️ Notas Importantes

- El sistema complementa (no reemplaza) el corrector del navegador
- Las sugerencias son automáticas basadas en el diccionario
- Si una palabra no está en el diccionario, ofrece capitalización básica
- El usuario siempre tiene la opción de ignorar las sugerencias

## 🚀 Próximas Mejoras Posibles

1. Integración con API de corrección ortográfica más avanzada
2. Aprendizaje de nombres frecuentes del sistema
3. Sugerencias basadas en similitud fonética
4. Corrección automática opcional
5. Diccionario personalizable desde la interfaz

---

**Versión**: 1.0  
**Fecha**: 2026-01-26  
**Autor**: Sistema SAPAZ
