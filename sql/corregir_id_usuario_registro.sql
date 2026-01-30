-- ============================================================
-- Script para corregir el tipo de dato de id_usuario_registro
-- ============================================================
-- Este script corrige el tipo de dato de la columna 
-- id_usuario_registro en la tabla facturas y agrega su FK
-- ============================================================

USE agua;

-- ============================================================
-- PASO 1: Corregir el tipo de dato de id_usuario_registro
-- ============================================================
-- Cambiar de VARCHAR(100) a INT(11) para que coincida con
-- el tipo de dato de usuarios_sistema.id_usuario_sistema

ALTER TABLE `facturas`
MODIFY COLUMN `id_usuario_registro` INT(11) DEFAULT NULL;

-- ============================================================
-- PASO 2: Agregar la clave foránea para id_usuario_registro
-- ============================================================

ALTER TABLE `facturas`
ADD CONSTRAINT `fk_factura_usuario_registro`
FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios_sistema` (`id_usuario_sistema`)
ON DELETE RESTRICT
ON UPDATE RESTRICT;

-- ============================================================
-- RESUMEN COMPLETO DE FOREIGN KEYS:
-- ============================================================
-- Tabla: cargos
--   - fk_cargo_lectura: id_lectura -> lecturas(id_lectura)
--
-- Tabla: facturas
--   - fk_factura_usuario: id_usuario -> usuarios_servicio(id_usuario)
--   - fk_factura_lectura: id_lectura -> lecturas(id_lectura)
--   - fk_factura_usuario_registro: id_usuario_registro -> usuarios_sistema(id_usuario_sistema)
--
-- Tabla: lecturas
--   - fk_lectura_usuario: id_usuario -> usuarios_servicio(id_usuario)
--   - fk_lectura_usuario_sistema: id_usuario_sistema -> usuarios_sistema(id_usuario_sistema)
--
-- Tabla: usuarios_servicio
--   - fk_usuario_domicilio: id_domicilio -> domicilios(id_domicilio)
-- ============================================================

SELECT 'Tipo de dato y clave foránea de id_usuario_registro corregidos exitosamente' AS Resultado;
