-- Script para crear usuarios del sistema SAPAZ (ACTUALIZADO)
-- Fecha: 2026-01-29
-- Descripción: Creación de usuarios lecturistas y administrador con hashes correctos

-- IMPORTANTE: Este script REEMPLAZA el anterior
-- Las contraseñas ahora están correctamente hasheadas

-- Contraseñas:
-- Todos los lecturistas: lecturista123
-- Administrador (Lucio Pérez): admin123

-- Insertar lecturistas
INSERT INTO `usuarios_sistema` (`nombre`, `usuario`, `contrasena`, `rol`, `activo`) VALUES
('Daniel Arellano Roldán', 'daniel.arellano', '$2y$12$uL5aiMamPWyjZyIqoE7RN.3m.ht/AJLbOkod7trERLIhfCsmrjUbe', 'LECTURISTA', 1),
('Antolín Escalante Rojas', 'antolin.escalante', '$2y$12$uL5aiMamPWyjZyIqoE7RN.3m.ht/AJLbOkod7trERLIhfCsmrjUbe', 'LECTURISTA', 1),
('Gaudencio Gutierrez Palacios', 'gaudencio.gutierrez', '$2y$12$uL5aiMamPWyjZyIqoE7RN.3m.ht/AJLbOkod7trERLIhfCsmrjUbe', 'LECTURISTA', 1),
('Crisoforo Gutierrez Pérez', 'crisoforo.gutierrez', '$2y$12$uL5aiMamPWyjZyIqoE7RN.3m.ht/AJLbOkod7trERLIhfCsmrjUbe', 'LECTURISTA', 1),
('Rodrigo Pérez Pérez', 'rodrigo.perez', '$2y$12$uL5aiMamPWyjZyIqoE7RN.3m.ht/AJLbOkod7trERLIhfCsmrjUbe', 'LECTURISTA', 1),
('Edmundo Reyes Pérez', 'edmundo.reyes', '$2y$12$uL5aiMamPWyjZyIqoE7RN.3m.ht/AJLbOkod7trERLIhfCsmrjUbe', 'LECTURISTA', 1);

-- Insertar administrador
INSERT INTO `usuarios_sistema` (`nombre`, `usuario`, `contrasena`, `rol`, `activo`) VALUES
('Lucio Pérez', 'lucio.perez', '$2y$12$z3lSRX2VcEOBuJ30Y7z6M.sqTXI4m96qv5RO08ek4msUTrP9D1cay', 'ADMIN', 1);

-- Verificar usuarios creados
SELECT id_usuario_sistema, nombre, usuario, rol, activo FROM usuarios_sistema ORDER BY rol DESC, nombre ASC;
