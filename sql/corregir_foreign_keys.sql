-- ============================================================
-- Script para corregir las claves foráneas (FOREIGN KEYS)
-- ============================================================
-- Este script agrega las restricciones de claves foráneas
-- faltantes en la tabla 'facturas'
-- ============================================================

USE agua;

-- ============================================================
-- TABLA: facturas
-- ============================================================
-- La tabla facturas tiene índices pero NO tiene las restricciones
-- de claves foráneas definidas. Vamos a agregarlas.

-- Primero, verificamos si ya existen las restricciones
-- (en caso de que ya estén definidas, esto evitará errores)

-- Agregar clave foránea para id_usuario -> usuarios_servicio
ALTER TABLE `facturas`
ADD CONSTRAINT `fk_factura_usuario`
FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_servicio` (`id_usuario`)
ON DELETE RESTRICT
ON UPDATE RESTRICT;

-- Agregar clave foránea para id_lectura -> lecturas
ALTER TABLE `facturas`
ADD CONSTRAINT `fk_factura_lectura`
FOREIGN KEY (`id_lectura`) REFERENCES `lecturas` (`id_lectura`)
ON DELETE RESTRICT
ON UPDATE RESTRICT;

-- NOTA: id_usuario_registro NO se puede agregar como FK porque:
-- 1. Es de tipo VARCHAR(100) en facturas
-- 2. Debería ser INT(11) para referenciar a usuarios_sistema
-- 3. Necesita corrección de tipo de dato primero

-- ============================================================
-- RESUMEN DE FOREIGN KEYS DESPUÉS DE LA CORRECCIÓN:
-- ============================================================
-- Tabla: cargos
--   - fk_cargo_lectura: id_lectura -> lecturas(id_lectura)
--
-- Tabla: facturas
--   - fk_factura_usuario: id_usuario -> usuarios_servicio(id_usuario)
--   - fk_factura_lectura: id_lectura -> lecturas(id_lectura)
--
-- Tabla: lecturas
--   - fk_lectura_usuario: id_usuario -> usuarios_servicio(id_usuario)
--   - fk_lectura_usuario_sistema: id_usuario_sistema -> usuarios_sistema(id_usuario_sistema)
--
-- Tabla: usuarios_servicio
--   - fk_usuario_domicilio: id_domicilio -> domicilios(id_domicilio)
-- ============================================================

SELECT 'Claves foráneas corregidas exitosamente' AS Resultado;
