-- Script para agregar columna 'pagado' a la tabla lecturas
-- Ejecutar este script en phpMyAdmin o desde la línea de comandos de MySQL
-- Fecha: 2026-01-24

-- Agregar columna pagado a la tabla lecturas
ALTER TABLE `lecturas` 
ADD COLUMN `pagado` ENUM('SI','NO') DEFAULT 'NO' AFTER `observaciones`;

-- Actualizar lecturas existentes que tienen facturas pagadas
UPDATE `lecturas` l
INNER JOIN `facturas` f ON l.id_lectura = f.id_lectura
SET l.pagado = 'SI'
WHERE f.estado = 'Pagado';

-- Verificar los cambios
SELECT * FROM `lecturas` LIMIT 10;
