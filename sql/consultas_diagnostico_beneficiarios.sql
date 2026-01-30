-- ============================================
-- CONSULTAS DE DIAGNÓSTICO - BENEFICIARIOS
-- VERSIÓN MEJORADA (Compatible con INT y VARCHAR)
-- ============================================

-- 1. NÚMEROS DE CONTRATO DUPLICADOS (SIMPLE Y EFECTIVA)
SELECT 
    no_contrato,
    COUNT(*) as cantidad_duplicados,
    GROUP_CONCAT(nombre SEPARATOR ' | ') as beneficiarios_afectados,
    GROUP_CONCAT(id_usuario SEPARATOR ', ') as ids
FROM usuarios_servicio
WHERE no_contrato IS NOT NULL
GROUP BY no_contrato
HAVING COUNT(*) > 1
ORDER BY cantidad_duplicados DESC, no_contrato;

-- 2. NÚMEROS DE MEDIDOR DUPLICADOS (SIMPLE Y EFECTIVA)
SELECT 
    no_medidor,
    COUNT(*) as cantidad_duplicados,
    GROUP_CONCAT(nombre SEPARATOR ' | ') as beneficiarios_afectados,
    GROUP_CONCAT(id_usuario SEPARATOR ', ') as ids
FROM usuarios_servicio
WHERE no_medidor IS NOT NULL
GROUP BY no_medidor
HAVING COUNT(*) > 1
ORDER BY cantidad_duplicados DESC, no_medidor;

-- 3. BENEFICIARIOS SIN NÚMERO DE CONTRATO
SELECT 
    id_usuario,
    nombre,
    no_medidor,
    fecha_alta
FROM usuarios_servicio
WHERE no_contrato IS NULL
ORDER BY fecha_alta DESC;

-- 4. BENEFICIARIOS SIN NÚMERO DE MEDIDOR
SELECT 
    id_usuario,
    nombre,
    no_contrato,
    fecha_alta
FROM usuarios_servicio
WHERE no_medidor IS NULL
ORDER BY fecha_alta DESC;

-- 5. BENEFICIARIOS SIN CONTRATO NI MEDIDOR
SELECT 
    id_usuario,
    nombre,
    fecha_alta
FROM usuarios_servicio
WHERE no_contrato IS NULL
  AND no_medidor IS NULL
ORDER BY fecha_alta DESC;

-- 6. RESUMEN GENERAL
SELECT 
    COUNT(*) as total_beneficiarios,
    SUM(CASE WHEN no_contrato IS NOT NULL THEN 1 ELSE 0 END) as con_numero_contrato,
    SUM(CASE WHEN no_medidor IS NOT NULL THEN 1 ELSE 0 END) as con_numero_medidor,
    SUM(CASE WHEN no_contrato IS NULL THEN 1 ELSE 0 END) as sin_numero_contrato,
    SUM(CASE WHEN no_medidor IS NULL THEN 1 ELSE 0 END) as sin_numero_medidor,
    COUNT(DISTINCT no_contrato) as contratos_unicos,
    COUNT(DISTINCT no_medidor) as medidores_unicos
FROM usuarios_servicio;

-- 7. DETALLE COMPLETO DE DUPLICADOS DE CONTRATO
-- Muestra TODOS los beneficiarios que tienen un contrato duplicado
SELECT 
    u1.id_usuario,
    u1.nombre,
    u1.no_contrato,
    u1.no_medidor,
    u1.fecha_alta,
    (SELECT COUNT(*) 
     FROM usuarios_servicio u2 
     WHERE u2.no_contrato = u1.no_contrato) as total_con_este_contrato,
    (SELECT GROUP_CONCAT(CONCAT(id_usuario, ': ', nombre) SEPARATOR ' | ')
     FROM usuarios_servicio u2
     WHERE u2.no_contrato = u1.no_contrato
       AND u2.id_usuario != u1.id_usuario) as otros_beneficiarios
FROM usuarios_servicio u1
WHERE u1.no_contrato IN (
    SELECT no_contrato
    FROM usuarios_servicio
    WHERE no_contrato IS NOT NULL
    GROUP BY no_contrato
    HAVING COUNT(*) > 1
)
ORDER BY u1.no_contrato, u1.fecha_alta;

-- 8. DETALLE COMPLETO DE DUPLICADOS DE MEDIDOR
-- Muestra TODOS los beneficiarios que tienen un medidor duplicado
SELECT 
    u1.id_usuario,
    u1.nombre,
    u1.no_contrato,
    u1.no_medidor,
    u1.fecha_alta,
    (SELECT COUNT(*) 
     FROM usuarios_servicio u2 
     WHERE u2.no_medidor = u1.no_medidor) as total_con_este_medidor,
    (SELECT GROUP_CONCAT(CONCAT(id_usuario, ': ', nombre) SEPARATOR ' | ')
     FROM usuarios_servicio u2
     WHERE u2.no_medidor = u1.no_medidor
       AND u2.id_usuario != u1.id_usuario) as otros_beneficiarios
FROM usuarios_servicio u1
WHERE u1.no_medidor IN (
    SELECT no_medidor
    FROM usuarios_servicio
    WHERE no_medidor IS NOT NULL
    GROUP BY no_medidor
    HAVING COUNT(*) > 1
)
ORDER BY u1.no_medidor, u1.fecha_alta;

-- 9. CONTAR CUÁNTOS NÚMEROS ESTÁN DUPLICADOS
SELECT 
    (SELECT COUNT(*) FROM (
        SELECT no_contrato 
        FROM usuarios_servicio 
        WHERE no_contrato IS NOT NULL
        GROUP BY no_contrato 
        HAVING COUNT(*) > 1
    ) as t1) as contratos_duplicados,
    (SELECT COUNT(*) FROM (
        SELECT no_medidor 
        FROM usuarios_servicio 
        WHERE no_medidor IS NOT NULL
        GROUP BY no_medidor 
        HAVING COUNT(*) > 1
    ) as t2) as medidores_duplicados;

-- 10. BENEFICIARIOS CON DATOS COMPLETOS
SELECT 
    id_usuario,
    nombre,
    no_contrato,
    no_medidor,
    fecha_alta
FROM usuarios_servicio
WHERE no_contrato IS NOT NULL 
  AND no_medidor IS NOT NULL
ORDER BY fecha_alta DESC;

-- ============================================
-- CONSULTAS EXTRA ÚTILES
-- ============================================

-- 11. VER TODOS LOS BENEFICIARIOS (para verificar datos)
SELECT 
    id_usuario,
    nombre,
    no_contrato,
    no_medidor,
    fecha_alta,
    CASE 
        WHEN no_contrato IS NULL AND no_medidor IS NULL THEN '⚠️ Sin contrato ni medidor'
        WHEN no_contrato IS NULL THEN '⚠️ Sin contrato'
        WHEN no_medidor IS NULL THEN '⚠️ Sin medidor'
        ELSE '✓ Completo'
    END as estado
FROM usuarios_servicio
ORDER BY fecha_alta DESC;

-- 12. BUSCAR UN MEDIDOR ESPECÍFICO (reemplaza 12345678 con el número que buscas)
SELECT 
    id_usuario,
    nombre,
    no_contrato,
    no_medidor,
    fecha_alta
FROM usuarios_servicio
WHERE no_medidor = 12345678;

-- 13. BUSCAR UN CONTRATO ESPECÍFICO (reemplaza 123 con el número que buscas)
SELECT 
    id_usuario,
    nombre,
    no_contrato,
    no_medidor,
    fecha_alta
FROM usuarios_servicio
WHERE no_contrato = 123;

-- 14. VER SOLO LOS MEDIDORES QUE ESTÁN DUPLICADOS (lista simple)
SELECT DISTINCT no_medidor
FROM usuarios_servicio
WHERE no_medidor IS NOT NULL
GROUP BY no_medidor
HAVING COUNT(*) > 1
ORDER BY no_medidor;

-- 15. VER SOLO LOS CONTRATOS QUE ESTÁN DUPLICADOS (lista simple)
SELECT DISTINCT no_contrato
FROM usuarios_servicio
WHERE no_contrato IS NOT NULL
GROUP BY no_contrato
HAVING COUNT(*) > 1
ORDER BY no_contrato;
