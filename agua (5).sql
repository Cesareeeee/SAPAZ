-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-01-2026 a las 23:17:29
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `agua`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id_cargo` int(11) NOT NULL,
  `id_lectura` int(11) NOT NULL,
  `tipo_cargo` enum('SERVICIO','RECARGO','MULTA') NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_cargo` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `clave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('tarifa_excedente', '15.05'),
('tarifa_m3', '10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `domicilios`
--

CREATE TABLE `domicilios` (
  `id_domicilio` int(11) NOT NULL,
  `calle` varchar(100) NOT NULL,
  `barrio` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `domicilios`
--

INSERT INTO `domicilios` (`id_domicilio`, `calle`, `barrio`) VALUES
(1, 'AVENIDA DEL TRABAJO', 'Centro'),
(2, 'CALLE JUAN DIEGO', 'Centro'),
(3, 'CALLE LINDAVISTA', 'Centro'),
(4, 'CALLE CONCORDIA', 'Centro'),
(5, 'CALLE 5 DE FEBRERO', 'Centro'),
(6, 'CALLE BENAVENTE', 'Centro'),
(7, 'CALLE CAMPO FLORIDO', 'Centro'),
(8, 'AVENIDA 6 DE DICIEMBRE', 'Centro'),
(9, 'CALLE LAS PALMAS', 'Centro'),
(10, 'CALLE LAS HUERTAS', 'Centro'),
(11, 'CALLE BENAVENTE', 'Centro'),
(12, 'CALLE FRAY PEDRO DE GANTE', 'TENANCO'),
(13, 'Calle Principal', 'Centro'),
(14, 'Calle Principal', 'Centro'),
(15, 'Avenida Libertad', 'Norte'),
(16, 'Calle del Sol', 'Sur'),
(17, 'Boulevard Reforma', 'Este'),
(18, 'Calle Comercio', 'Oeste'),
(19, 'Avenida Paz', 'Centro'),
(20, 'Calle Esperanza', 'Norte'),
(21, 'Boulevard Progreso', 'Sur'),
(22, 'Calle Unión', 'Este'),
(23, 'Avenida Futuro', 'Oeste'),
(24, 'Calle Armonía', 'Centro'),
(25, 'Boulevard Victoria', 'Norte'),
(26, 'Calle Paz', 'Sur'),
(27, 'Avenida Esperanza', 'Este'),
(28, 'Calle Progreso', 'Oeste'),
(29, 'Boulevard Unión', 'Centro'),
(30, 'Calle Futuro', 'Norte'),
(31, 'Avenida Armonía', 'Sur'),
(32, 'Calle Victoria', 'Este'),
(33, 'Boulevard Paz', 'Oeste');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_factura` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_lectura` int(11) DEFAULT NULL,
  `id_usuario_registro` int(11) DEFAULT NULL,
  `fecha_emision` datetime DEFAULT current_timestamp(),
  `monto_total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Pagado','Cancelado') DEFAULT 'Pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id_factura`, `id_usuario`, `id_lectura`, `id_usuario_registro`, `fecha_emision`, `monto_total`, `estado`, `created_at`) VALUES
(9, 135, 250, NULL, '2026-01-24 19:24:38', 170.00, 'Pendiente', '2026-01-25 01:24:38'),
(10, 135, 251, NULL, '2026-01-24 19:28:03', 160.00, 'Pagado', '2026-01-25 01:28:03'),
(11, 135, 252, NULL, '2026-01-24 19:46:05', 456580.00, 'Cancelado', '2026-01-25 01:46:05'),
(12, 135, 253, NULL, '2026-01-24 19:54:32', -455480.00, 'Pagado', '2026-01-25 01:54:32'),
(13, 135, 256, NULL, '2026-01-24 21:08:19', 300.00, 'Pendiente', '2026-01-25 03:08:19'),
(14, 135, 254, NULL, '2026-01-24 21:26:23', 280.00, 'Pagado', '2026-01-25 03:26:23'),
(15, 135, 255, NULL, '2026-01-24 21:37:38', 270.00, 'Pagado', '2026-01-25 03:37:38'),
(16, 135, 258, NULL, '2026-01-24 22:03:36', 1425.00, 'Pagado', '2026-01-25 04:03:36'),
(17, 135, 263, NULL, '2026-01-26 12:13:30', 600.00, 'Pendiente', '2026-01-26 18:13:30'),
(18, 135, 260, NULL, '2026-01-26 12:15:22', -20000.00, 'Pendiente', '2026-01-26 18:15:22'),
(19, 135, 261, NULL, '2026-01-26 12:19:12', 675.00, 'Pagado', '2026-01-26 18:19:12'),
(20, 135, 262, NULL, '2026-01-26 12:22:34', 100.00, 'Pagado', '2026-01-26 18:22:34'),
(21, 135, 264, NULL, '2026-01-26 12:23:34', 350.00, 'Pagado', '2026-01-26 18:23:34'),
(22, 139, 265, NULL, '2026-01-26 12:35:23', 240.00, 'Pagado', '2026-01-26 18:35:23'),
(23, 135, 259, NULL, '2026-01-26 13:03:31', 6210.00, 'Pendiente', '2026-01-26 19:03:31'),
(24, 135, 266, NULL, '2026-01-26 13:09:13', 8928.00, 'Pagado', '2026-01-26 19:09:13'),
(25, 135, 267, NULL, '2026-01-26 13:18:07', 6234.00, 'Pendiente', '2026-01-26 19:18:07'),
(26, 135, 268, NULL, '2026-01-26 13:35:21', 230.00, 'Pagado', '2026-01-26 19:35:21'),
(27, 135, 269, NULL, '2026-01-26 13:47:14', 309.00, 'Pagado', '2026-01-26 19:47:14'),
(28, 135, 270, NULL, '2026-01-26 14:00:16', 6990.00, 'Pagado', '2026-01-26 20:00:16'),
(29, 135, 271, NULL, '2026-01-26 14:03:50', 6894.00, 'Pagado', '2026-01-26 20:03:50'),
(30, 135, 272, NULL, '2026-01-26 14:07:04', 14835060.00, 'Pagado', '2026-01-26 20:07:04'),
(31, 135, 273, NULL, '2026-01-26 14:11:40', 4636575.00, 'Pagado', '2026-01-26 20:11:40'),
(32, 135, 274, NULL, '2026-01-26 14:12:07', 99999999.99, 'Pagado', '2026-01-26 20:12:07'),
(33, 135, 275, NULL, '2026-01-26 14:25:51', 99999999.99, 'Pagado', '2026-01-26 20:25:51'),
(34, 135, 276, NULL, '2026-01-26 14:32:13', -99999999.99, 'Pagado', '2026-01-26 20:32:13'),
(35, 135, 278, NULL, '2026-01-26 15:55:11', 3876.00, 'Pagado', '2026-01-26 21:55:11'),
(36, 135, 283, NULL, '2026-01-26 15:59:01', -6431420.00, 'Pagado', '2026-01-26 21:59:01'),
(37, 135, 279, NULL, '2026-01-26 16:02:20', 348000.00, 'Pagado', '2026-01-26 22:02:20'),
(38, 135, 280, NULL, '2026-01-26 16:03:34', -197880.00, 'Pagado', '2026-01-26 22:03:34'),
(39, 135, 282, NULL, '2026-01-26 16:16:26', 9725610.00, 'Pagado', '2026-01-26 22:16:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecturas`
--

CREATE TABLE `lecturas` (
  `id_lectura` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_lectura` date NOT NULL,
  `lectura_anterior` decimal(10,2) NOT NULL,
  `lectura_actual` decimal(10,2) NOT NULL,
  `consumo_m3` decimal(10,2) NOT NULL,
  `id_usuario_sistema` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lecturas`
--

INSERT INTO `lecturas` (`id_lectura`, `id_usuario`, `fecha_lectura`, `lectura_anterior`, `lectura_actual`, `consumo_m3`, `id_usuario_sistema`, `observaciones`, `created_at`) VALUES
(278, 135, '2026-01-26', 0.00, 1222.00, 1222.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-26 21:31:17'),
(279, 135, '2026-01-26', 1222.00, 24432.00, 23210.00, 2, 'Consumo superior a 30 metros cúbicos.', '2026-01-26 21:33:30'),
(280, 135, '2026-01-26', 24432.00, 4644.00, -19788.00, 4, 'El contador del medidor retrocedió.', '2026-01-26 21:34:07'),
(281, 135, '2026-01-26', 4644.00, 234.00, -4410.00, 2, 'El contador del medidor retrocedió.', '2026-01-26 21:45:15'),
(282, 135, '2026-01-26', 234.00, 646464.00, 646230.00, 4, 'Consumo superior a 30 metros cúbicos.', '2026-01-26 21:45:37'),
(283, 135, '2026-01-26', 646464.00, 3322.00, -643142.00, 2, 'El contador del medidor retrocedió.', '2026-01-26 21:47:13'),
(284, 135, '2026-01-26', 3322.00, 3433.00, 111.00, 2, 'Consumo superior a 30 metros cúbicos.', '2026-01-26 21:48:42'),
(285, 135, '2026-01-26', 3433.00, 3454.00, 21.00, 2, '', '2026-01-26 21:50:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_servicio`
--

CREATE TABLE `usuarios_servicio` (
  `id_usuario` int(11) NOT NULL,
  `no_contrato` varchar(30) NOT NULL,
  `no_medidor` varchar(30) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `id_domicilio` int(11) NOT NULL,
  `fecha_alta` date NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `nombre_anterior` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_servicio`
--

INSERT INTO `usuarios_servicio` (`id_usuario`, `no_contrato`, `no_medidor`, `nombre`, `id_domicilio`, `fecha_alta`, `activo`, `created_at`, `nombre_anterior`) VALUES
(135, '23456', '2345689', 'Julio Cesar Ruiz Pérez GUAPO', 8, '2026-01-25', 1, '2026-01-25 01:23:16', 'Julio Cesar Ruiz Pérez'),
(139, '34567', '34567', 'Elena Rivera Mendoza', 8, '2026-01-26', 1, '2026-01-26 18:34:47', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_sistema`
--

CREATE TABLE `usuarios_sistema` (
  `id_usuario_sistema` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('ADMIN','LECTURISTA') NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_sistema`
--

INSERT INTO `usuarios_sistema` (`id_usuario_sistema`, `nombre`, `usuario`, `contrasena`, `rol`, `activo`, `created_at`) VALUES
(1, 'Administrador Principal', 'admin', '0192023a7bbd73250516f069df18b500', 'ADMIN', 1, '2026-01-10 23:49:11'),
(2, 'Julio Cesar Ruiz Perez', '7u7thin@gmail.com', '$2y$12$AYQ3kuxxrImy.typur/RQerfbA2ElSXtCb6GnVi4MMZsw1Oc2qzgK', 'LECTURISTA', 1, '2026-01-26 21:01:18'),
(3, 'Julio Cesar', 'fockiu', '$2y$12$WiOous.XxrrnEAwUuIVECuU4BZb6GPTtu.0qIeM9t6eGi8JsKnD7y', 'LECTURISTA', 1, '2026-01-26 21:05:03'),
(4, 'Julio Cesar Ruiz Perez', 'crkendok@gmail.com', '$2y$12$VzKh9DXGwYTLqoaDJNXFpO1EjpaeFQm8UYfS/dt7WVOHEc6iKx7iK', 'LECTURISTA', 1, '2026-01-26 21:08:25');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id_cargo`),
  ADD KEY `fk_cargo_lectura` (`id_lectura`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `domicilios`
--
ALTER TABLE `domicilios`
  ADD PRIMARY KEY (`id_domicilio`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id_factura`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_lectura` (`id_lectura`),
  ADD KEY `id_usuario_registro` (`id_usuario_registro`);

--
-- Indices de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD PRIMARY KEY (`id_lectura`),
  ADD KEY `fk_lectura_usuario` (`id_usuario`),
  ADD KEY `fk_lectura_usuario_sistema` (`id_usuario_sistema`);

--
-- Indices de la tabla `usuarios_servicio`
--
ALTER TABLE `usuarios_servicio`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `no_contrato` (`no_contrato`),
  ADD UNIQUE KEY `no_medidor` (`no_medidor`),
  ADD KEY `fk_usuario_domicilio` (`id_domicilio`);

--
-- Indices de la tabla `usuarios_sistema`
--
ALTER TABLE `usuarios_sistema`
  ADD PRIMARY KEY (`id_usuario_sistema`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id_cargo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `domicilios`
--
ALTER TABLE `domicilios`
  MODIFY `id_domicilio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  MODIFY `id_lectura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=286;

--
-- AUTO_INCREMENT de la tabla `usuarios_servicio`
--
ALTER TABLE `usuarios_servicio`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT de la tabla `usuarios_sistema`
--
ALTER TABLE `usuarios_sistema`
  MODIFY `id_usuario_sistema` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD CONSTRAINT `fk_cargo_lectura` FOREIGN KEY (`id_lectura`) REFERENCES `lecturas` (`id_lectura`);

--
-- Filtros para la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD CONSTRAINT `fk_lectura_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_servicio` (`id_usuario`),
  ADD CONSTRAINT `fk_lectura_usuario_sistema` FOREIGN KEY (`id_usuario_sistema`) REFERENCES `usuarios_sistema` (`id_usuario_sistema`);

--
-- Filtros para la tabla `usuarios_servicio`
--
ALTER TABLE `usuarios_servicio`
  ADD CONSTRAINT `fk_usuario_domicilio` FOREIGN KEY (`id_domicilio`) REFERENCES `domicilios` (`id_domicilio`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
