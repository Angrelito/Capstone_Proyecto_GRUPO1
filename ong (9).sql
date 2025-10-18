-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-10-2025 a las 05:54:28
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
-- Base de datos: `ong`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `beneficiarios`
--

CREATE TABLE `beneficiarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `tipo_ayuda` enum('Mueble','Ropa','Artefacto','Electronico') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `beneficiarios`
--

INSERT INTO `beneficiarios` (`id`, `nombre`, `apellido`, `dni`, `telefono`, `direccion`, `tipo_ayuda`, `descripcion`, `empleado_id`, `fecha_registro`) VALUES
(1, 'Cesar', 'Figueroa', '78293445', '941828934', 'Av. Naranjal', 'Mueble', '23232', 4, '2025-10-18 02:12:30'),
(2, 'Nicole', 'WSDSDS', '34234324', '34343', 'sdsd', 'Ropa', 'dwd', 4, '2025-10-18 02:40:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donaciones`
--

CREATE TABLE `donaciones` (
  `id` int(11) NOT NULL,
  `donante_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `tipo_donacion` enum('Mueble','Ropa','Artefacto','Electrónico') NOT NULL,
  `cantidad` int(11) DEFAULT 1,
  `descripcion` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `donaciones`
--

INSERT INTO `donaciones` (`id`, `donante_id`, `empleado_id`, `tipo_donacion`, `cantidad`, `descripcion`, `fecha`) VALUES
(2, 3, 5, 'Mueble', 1, 'hola.', '2025-10-06 22:25:52'),
(3, 3, 5, 'Mueble', 1, 'La cama esta bien conservada.', '2025-10-07 00:59:58'),
(4, 15, 5, 'Mueble', 2, 'Los dos roperos esta bien conservados.', '2025-10-07 01:01:03'),
(5, 15, 4, 'Mueble', 12, '1223', '2025-10-16 17:55:10'),
(6, 15, 4, 'Mueble', 5, 'Soda pop', '2025-10-16 18:02:13'),
(7, 15, 4, 'Mueble', 3, 'Soda pop', '2025-10-16 18:05:45'),
(8, 15, 4, 'Mueble', 5, '1234545454', '2025-10-16 18:06:12'),
(9, 14, 4, 'Mueble', 8, 'Hola', '2025-10-18 03:26:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donantes`
--

CREATE TABLE `donantes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `donacion` varchar(100) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `donantes`
--

INSERT INTO `donantes` (`id`, `nombre`, `correo`, `telefono`, `donacion`, `descripcion`, `fecha`) VALUES
(1, 'Kristhian Guera Rodriguez', 'angrepitop@gmail.com', '843345934', 'Cama', 'dsdwee', '2025-09-22 03:11:07'),
(2, 'Kristhian Guerra Rodriguez', 'redsnowcinderella@gmail.com', '945890943', 'Cama', '123242424wedadsd', '2025-09-22 04:36:20'),
(3, 'Kristhian Guerra Rodriguez', 'angrepitop@gmail.com', '945890943', 'Cama', 'sdsdsd', '2025-09-22 04:42:34'),
(4, 'Kristhian Guerra Rodriguez', 'angrepitop@gmail.com', '945890943', 'Silla', 'ryryr', '2025-09-22 04:43:00'),
(6, 'Kristhian Guerra Rodriguez', 'angrepitop@gmail.com', '945890943', 'Ropero', 'sdsdsd', '2025-09-22 05:48:52'),
(7, 'Kristhian Guerra Rodriguez', 'angrepitop@gmail.com', '945890943', 'Mesa', '32323q24', '2025-09-22 06:02:48'),
(8, 'Nicole', 'redsnowcinderella@gmail.com', '834435678', 'Pantalón', 'dsdadasda', '2025-09-22 20:45:14'),
(9, 'Nicole', 'redsnowcinderella@gmail.com', '834834853', 'Mesa', 'me encantaria donar mi mesa.', '2025-09-23 01:50:43'),
(10, 'Nicole', 'pablo@gmail.com', '434343545', 'Alfombra', 'Quiero donar una alfombra.', '2025-09-23 02:15:28'),
(11, 'Nicole', 'redsnowcinderella@gmail.com', '834934854', 'Alfombra', 'Me encanta donar.', '2025-09-23 02:18:21'),
(12, 'Tester', 'tester@gmail.com', '987654321', 'Mesa', 'Donación de prueba desde test.php', '2025-09-23 03:21:50'),
(13, 'Tester PHPUnit', 'testerphpunit@gmail.com', '987654321', 'Mesa', 'Donación de prueba con PHPUnit', '2025-09-23 03:47:25'),
(14, 'Tester PHPUnit', 'testerphpunit@gmail.com', '987654321', 'Mesa', 'Donación de prueba con PHPUnit', '2025-09-23 03:50:32'),
(15, 'Nicole', 'nayelidazacanchanya@gmail.com', '834958454', 'Ropero', 'Me gustaria donar un ropero viejo.', '2025-09-23 04:06:45'),
(16, 'Nayeli', 'nayelidazacanchanya@gmail.com', '934859685', 'Alfombra', 'Me gustaria donar una alfombra.', '2025-09-23 04:07:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `usuario`, `password`, `email`, `creado_en`) VALUES
(3, 'Diego', '$2y$10$yNnG3SU.PCkmKcq7EmSEfeJ/uZ/02eYSEuaA1YAfX2Sg8tmQzWdV6', 'hollowsilksongpablo@gmail.com', '2025-09-18 03:35:34'),
(4, 'Silver', '$2y$10$XKwxePAEOYyU66qVVejFPOyR8ifx24Tf2/yo7EGF4sC6L5YKJxAAy', 'shermanhornet@gmail.com', '2025-09-18 03:55:06'),
(5, 'Cristhian', '$2y$10$vLo6tm6f3mC0E0t8TnSG0ubhtINBExod.yzZCO.KRiDDiuyR1fj2O', 'jesuskristhianguerra@gmail.com', '2025-10-06 20:54:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_inventario`
--

CREATE TABLE `historial_inventario` (
  `id` int(11) NOT NULL,
  `objeto_donado` varchar(100) NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `cantidad_total` int(11) NOT NULL,
  `generado_por` varchar(100) NOT NULL,
  `fecha_reporte` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_inventario`
--

INSERT INTO `historial_inventario` (`id`, `objeto_donado`, `categoria`, `cantidad_total`, `generado_por`, `fecha_reporte`) VALUES
(1, 'Cama', 'Mueble', 2, 'Silver', '2025-10-13 15:19:40'),
(2, 'Ropero', 'Mueble', 2, 'Silver', '2025-10-13 15:19:40'),
(3, 'Cama', 'Mueble', 2, 'Silver', '2025-10-13 16:01:26'),
(4, 'Ropero', 'Mueble', 2, 'Silver', '2025-10-13 16:01:26'),
(5, 'Cama', 'Mueble', 2, 'Silver', '2025-10-13 16:01:33'),
(6, 'Ropero', 'Mueble', 2, 'Silver', '2025-10-13 16:01:33'),
(7, 'Cama', 'Mueble', 2, 'Silver', '2025-10-13 16:05:03'),
(8, 'Ropero', 'Mueble', 2, 'Silver', '2025-10-13 16:05:03'),
(9, 'Cama', 'Mueble', 2, 'Silver', '2025-10-13 17:33:07'),
(10, 'Ropero', 'Mueble', 2, 'Silver', '2025-10-13 17:33:07'),
(11, 'Cama', 'Mueble', 2, 'Silver', '2025-10-16 12:54:32'),
(12, 'Ropero', 'Mueble', 2, 'Silver', '2025-10-16 12:54:32'),
(13, 'Ropero', 'Mueble', 8, 'Silver', '2025-10-16 14:51:21'),
(14, 'Ropero', 'Mueble', 8, 'Silver', '2025-10-17 21:26:46'),
(15, 'Ropero', 'Mueble', 8, 'Silver', '2025-10-17 22:27:37'),
(16, 'Mesa', 'Mueble', 8, 'Silver', '2025-10-17 22:27:55'),
(17, 'Ropero', 'Mueble', 8, 'Silver', '2025-10-17 22:27:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id` int(11) NOT NULL,
  `donacion_id` int(11) NOT NULL,
  `objeto_donado` varchar(100) NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `cantidad_disponible` int(11) DEFAULT 0,
  `ultima_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id`, `donacion_id`, `objeto_donado`, `categoria`, `cantidad_disponible`, `ultima_actualizacion`) VALUES
(2, 7, 'Ropero', 'Mueble', 8, '2025-10-16 13:06:12'),
(3, 9, 'Mesa', 'Mueble', 8, '2025-10-17 22:26:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `organizaciones`
--

CREATE TABLE `organizaciones` (
  `id` int(11) NOT NULL,
  `nombre_organizacion` varchar(150) NOT NULL,
  `representante_nombre` varchar(100) DEFAULT NULL,
  `representante_apellido` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `tipo_ayuda` enum('Mueble','Ropa','Artefacto','Electronico') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `retiro`
--

CREATE TABLE `retiro` (
  `id` int(11) NOT NULL,
  `donante_id` int(11) NOT NULL,
  `estado` enum('En proceso','Recibido','Cancelado') DEFAULT 'En proceso',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `empleado_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `retiro`
--

INSERT INTO `retiro` (`id`, `donante_id`, `estado`, `fecha_actualizacion`, `empleado_id`) VALUES
(1, 16, 'Cancelado', '2025-10-07 02:15:13', 5),
(2, 15, 'Recibido', '2025-10-07 02:15:17', 5),
(3, 14, 'Recibido', '2025-10-18 03:31:36', 4);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `beneficiarios`
--
ALTER TABLE `beneficiarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `donaciones`
--
ALTER TABLE `donaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donante_id` (`donante_id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `donantes`
--
ALTER TABLE `donantes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historial_inventario`
--
ALTER TABLE `historial_inventario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donacion_id` (`donacion_id`);

--
-- Indices de la tabla `organizaciones`
--
ALTER TABLE `organizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `retiro`
--
ALTER TABLE `retiro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donante_id` (`donante_id`),
  ADD KEY `fk_retiro_empleado` (`empleado_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `beneficiarios`
--
ALTER TABLE `beneficiarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `donaciones`
--
ALTER TABLE `donaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `donantes`
--
ALTER TABLE `donantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `historial_inventario`
--
ALTER TABLE `historial_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `organizaciones`
--
ALTER TABLE `organizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `retiro`
--
ALTER TABLE `retiro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `beneficiarios`
--
ALTER TABLE `beneficiarios`
  ADD CONSTRAINT `beneficiarios_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `donaciones`
--
ALTER TABLE `donaciones`
  ADD CONSTRAINT `donaciones_ibfk_1` FOREIGN KEY (`donante_id`) REFERENCES `donantes` (`id`),
  ADD CONSTRAINT `donaciones_ibfk_2` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`);

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`donacion_id`) REFERENCES `donaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `organizaciones`
--
ALTER TABLE `organizaciones`
  ADD CONSTRAINT `organizaciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `retiro`
--
ALTER TABLE `retiro`
  ADD CONSTRAINT `fk_retiro_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `retiro_ibfk_1` FOREIGN KEY (`donante_id`) REFERENCES `donantes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
