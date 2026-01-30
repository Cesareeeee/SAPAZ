-- ============================================================
-- Script para limpiar la base de datos AGUA
-- ============================================================
-- Este script elimina todos los datos de las tablas EXCEPTO:
-- - usuarios_sistema (se mantiene intacta)
-- - configuracion (se mantiene intacta)
-- 
-- También reinicia los contadores AUTO_INCREMENT
-- ============================================================

-- Deshabilitar temporalmente las restricciones de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- ELIMINAR DATOS DE LAS TABLAS
-- ============================================================

-- Tabla: cargos
TRUNCATE TABLE `cargos`;

-- Tabla: facturas
TRUNCATE TABLE `facturas`;

-- Tabla: lecturas
TRUNCATE TABLE `lecturas`;

-- Tabla: usuarios_servicio
TRUNCATE TABLE `usuarios_servicio`;

-- Tabla: domicilios
TRUNCATE TABLE `domicilios`;

-- ============================================================
-- REINICIAR CONTADORES AUTO_INCREMENT
-- ============================================================

-- Reiniciar contador de cargos
ALTER TABLE `cargos` AUTO_INCREMENT = 1;

-- Reiniciar contador de facturas
ALTER TABLE `facturas` AUTO_INCREMENT = 1;

-- Reiniciar contador de lecturas
ALTER TABLE `lecturas` AUTO_INCREMENT = 1;

-- Reiniciar contador de usuarios_servicio
ALTER TABLE `usuarios_servicio` AUTO_INCREMENT = 1;

-- Reiniciar contador de domicilios
ALTER TABLE `domicilios` AUTO_INCREMENT = 1;

-- ============================================================
-- NOTA: Las siguientes tablas NO se modifican:
-- - usuarios_sistema (mantiene todos sus datos y contador)
-- - configuracion (mantiene todos sus datos)
-- ============================================================

-- Rehabilitar las restricciones de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Mensaje de confirmación
SELECT 'Base de datos limpiada exitosamente. Tablas afectadas: cargos, facturas, lecturas, usuarios_servicio, domicilios' AS Resultado;
