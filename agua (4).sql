-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-01-2026 a las 07:27:04
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
  `fecha_emision` datetime DEFAULT current_timestamp(),
  `monto_total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Pagado','Cancelado') DEFAULT 'Pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id_factura`, `id_usuario`, `id_lectura`, `fecha_emision`, `monto_total`, `estado`, `created_at`) VALUES
(1, 59, 150, '2026-01-21 00:13:25', 170.00, 'Pagado', '2026-01-21 06:13:25'),
(2, 60, 144, '2026-01-21 00:16:28', 510.00, 'Pagado', '2026-01-21 06:16:28');

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
(137, 49, '2026-01-20', 0.00, 2345.00, 2345.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 05:22:47'),
(138, 48, '2026-01-20', 0.00, 355.00, 355.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 05:22:55'),
(139, 54, '2026-01-20', 0.00, 4567.00, 4567.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 05:23:22'),
(140, 53, '2026-01-20', 0.00, 345.00, 345.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 05:23:39'),
(141, 52, '2026-01-20', 0.00, 4646.00, 4646.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 05:25:21'),
(142, 51, '2026-01-20', 0.00, 9494.00, 9494.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 05:25:30'),
(144, 60, '2026-01-20', 0.00, 46.00, 46.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 05:25:47'),
(145, 50, '2026-01-20', 0.00, 464.00, 464.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 05:25:55'),
(146, 61, '2026-01-20', 0.00, 494.00, 494.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 05:26:02'),
(148, 58, '2026-01-20', 4949.00, 456789.00, 451840.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-20 06:04:08'),
(150, 59, '2026-01-21', 0.00, 12.00, 12.00, 1, '', '2026-01-21 02:08:29'),
(151, 59, '2026-01-21', 12.00, 10.00, -2.00, 1, 'El contador del medidor retrocedió.', '2026-01-21 02:11:33'),
(152, 67, '2026-01-21', 0.00, 234.00, 234.00, 1, 'Consumo superior a 30 metros cúbicos.', '2026-01-21 05:52:57'),
(233, 48, '2025-10-01', 120.00, 135.00, 15.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(234, 48, '2025-11-01', 135.00, 152.00, 17.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(235, 48, '2025-12-01', 152.00, 168.00, 16.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(236, 49, '2025-10-01', 80.00, 95.00, 15.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(237, 49, '2025-11-01', 95.00, 110.00, 15.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(238, 49, '2025-12-01', 110.00, 128.00, 18.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(239, 50, '2025-10-01', 200.00, 220.00, 20.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(240, 50, '2025-11-01', 220.00, 238.00, 18.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(241, 50, '2025-12-01', 238.00, 255.00, 17.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(242, 51, '2025-10-01', 90.00, 104.00, 14.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(243, 51, '2025-11-01', 104.00, 120.00, 16.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(244, 51, '2025-12-01', 120.00, 138.00, 18.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(245, 52, '2025-10-01', 60.00, 72.00, 12.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(246, 52, '2025-11-01', 72.00, 85.00, 13.00, 1, 'Lectura mensual', '2026-01-21 06:03:51'),
(247, 52, '2025-12-01', 85.00, 99.00, 14.00, 1, 'Lectura mensual', '2026-01-21 06:03:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_lectura` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `pagado` enum('SI','NO') DEFAULT 'NO',
  `fecha_pago` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id_tarifa` int(11) NOT NULL,
  `precio_m3` decimal(10,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id_ticket` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `folio` varchar(50) NOT NULL,
  `fecha_emision` datetime NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(48, 'CONT003', '1122334455', 'Carlos Ramírez Soto', 3, '2023-03-10', 1, '2026-01-15 04:42:08', NULL),
(49, 'CONT004', '5566778899', 'Ana González Martínez', 4, '2023-04-05', 1, '2026-01-15 04:42:08', NULL),
(50, 'CONT005', '4433221100', 'Luis Rodríguez Díaz', 5, '2023-05-12', 1, '2026-01-15 04:42:08', NULL),
(51, 'CONT006', '7788990011', 'Sofía Fernández Ruiz', 6, '2023-06-18', 1, '2026-01-15 04:42:08', NULL),
(52, 'CONT007', '3344556677', 'Miguel Torres Jiménez', 7, '2023-07-22', 1, '2026-01-15 04:42:08', NULL),
(53, 'CONT008', '8899001122', 'Laura Morales Vázquez', 8, '2023-08-30', 1, '2026-01-15 04:42:08', NULL),
(54, '34567890', '2233445566', 'Diego Sánchez Castillos', 9, '2023-09-14', 1, '2026-01-15 04:42:08', NULL),
(58, '234567', '4567890', 'Elena Salazar', 10, '2026-01-15', 1, '2026-01-15 05:35:06', 'VRG'),
(59, '546466410', '12345678910', 'Julio Cesar Ruiz Perez', 12, '2026-01-15', 1, '2026-01-15 05:38:49', 'Julio Cesar Ruiz Perez Hola'),
(60, '2345676543456765', '345678987654345678', 'Juan Pérez', 8, '2024-01-01', 1, '2026-01-15 05:51:25', NULL),
(61, 'C002', 'M002', 'María García', 1, '2024-01-01', 1, '2026-01-15 05:51:25', NULL),
(67, '23456', '345678', 'Francisco Antonio CACORRO', 12, '2026-01-21', 1, '2026-01-21 04:28:11', 'Francisco Antonio'),
(71, '234563456', '34567834567', 'Elena Rivera Mendoza', 3, '2026-01-21', 1, '2026-01-21 05:29:51', NULL),
(73, '45678934567', '456789023456', 'Elena Rivera Mendoza2345', 9, '2026-01-21', 1, '2026-01-21 05:44:03', NULL),
(114, 'C0031', 'M001', 'Juan Pérez García', 1, '2025-01-15', 1, '2025-01-15 16:00:00', NULL),
(115, 'C0023', 'M0032', 'María López Hernández', 2, '2025-02-10', 1, '2025-02-10 17:00:00', NULL),
(116, 'C003', 'M003', 'Carlos Rodríguez Martínez', 3, '2025-03-05', 1, '2025-03-05 18:00:00', NULL),
(117, 'C0033', 'M004', 'Ana González Sánchez', 4, '2025-04-20', 1, '2025-04-20 18:00:00', NULL),
(118, 'C005', 'M005', 'Pedro Ramírez Torres', 5, '2025-05-12', 1, '2025-05-12 19:00:00', NULL),
(119, 'C006', 'M006', 'Laura Díaz Flores', 6, '2025-06-08', 1, '2025-06-08 20:00:00', NULL),
(120, 'C007', 'M007', 'Miguel Morales Ruiz', 7, '2025-07-25', 1, '2025-07-25 21:00:00', NULL),
(121, 'C008', 'M008', 'Carmen Jiménez Castro', 8, '2025-08-14', 1, '2025-08-14 22:00:00', NULL),
(122, 'C009', 'M009', 'José Ortega Vargas', 9, '2025-09-30', 1, '2025-09-30 23:00:00', NULL),
(123, 'C010', 'M010', 'Isabel Delgado Mendoza', 10, '2025-10-18', 1, '2025-10-19 00:00:00', NULL),
(124, 'C011', 'M011', 'Antonio Herrera Silva', 11, '2025-11-22', 1, '2025-11-23 02:00:00', NULL),
(125, 'C012', 'M012', 'Rosa Medina Reyes', 12, '2025-12-05', 1, '2025-12-06 03:00:00', NULL),
(126, 'C013', 'M013', 'Francisco Castro Guerrero', 13, '2026-01-10', 1, '2026-01-11 04:00:00', NULL),
(127, 'C014', 'M014', 'Pilar Rubio Navarro', 14, '2026-01-15', 1, '2026-01-16 05:00:00', NULL),
(128, 'C015', 'M015', 'Manuel Vargas León', 15, '2026-01-20', 1, '2026-01-20 06:00:00', NULL),
(129, 'C016', 'M016', 'Teresa Moreno Peña', 16, '2026-01-25', 1, '2026-01-25 07:00:00', NULL),
(130, 'C017', 'M017', 'Ángel Domínguez Soto', 17, '2026-01-28', 1, '2026-01-28 08:00:00', NULL),
(131, 'C018', 'M018', 'Cristina Alonso Gil', 18, '2026-02-01', 1, '2026-02-01 09:00:00', NULL),
(132, 'C019', 'M019', 'Rafael Nieto Cabrera', 19, '2026-02-05', 1, '2026-02-05 10:00:00', NULL),
(133, 'C020', 'M020', 'Lucía Aguilar Ramos', 20, '2026-02-10', 1, '2026-02-10 11:00:00', NULL);

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
(1, 'Administrador Principal', 'admin', '0192023a7bbd73250516f069df18b500', 'ADMIN', 1, '2026-01-10 23:49:11');

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
  ADD KEY `id_lectura` (`id_lectura`);

--
-- Indices de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD PRIMARY KEY (`id_lectura`),
  ADD KEY `fk_lectura_usuario` (`id_usuario`),
  ADD KEY `fk_lectura_usuario_sistema` (`id_usuario_sistema`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `fk_pago_lectura` (`id_lectura`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id_tarifa`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id_ticket`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `fk_ticket_pago` (`id_pago`);

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
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  MODIFY `id_lectura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id_tarifa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id_ticket` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios_servicio`
--
ALTER TABLE `usuarios_servicio`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT de la tabla `usuarios_sistema`
--
ALTER TABLE `usuarios_sistema`
  MODIFY `id_usuario_sistema` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pago_lectura` FOREIGN KEY (`id_lectura`) REFERENCES `lecturas` (`id_lectura`);

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_ticket_pago` FOREIGN KEY (`id_pago`) REFERENCES `pagos` (`id_pago`);

--
-- Filtros para la tabla `usuarios_servicio`
--
ALTER TABLE `usuarios_servicio`
  ADD CONSTRAINT `fk_usuario_domicilio` FOREIGN KEY (`id_domicilio`) REFERENCES `domicilios` (`id_domicilio`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
