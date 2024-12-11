-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 18-11-2024 a las 19:46:52
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `plagio`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `alumnos_curso` (IN `curso_id` INT)   BEGIN
    SELECT CONCAT(u.nombre, ' ', u.apellidos) AS alumno
    FROM detallecurso dc
    JOIN usuarios u ON dc.alumno = u.id
    WHERE dc.curso = curso_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listar_noticias` (IN `curso_id` INT)   BEGIN
    SELECT *
    FROM noticias
    WHERE curso = curso_id AND estado = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `nombre_profesor` (IN `curso_id` INT)   BEGIN
    SELECT CONCAT(u.nombre, ' ', u.apellidos) AS profesor
    FROM cursos c
    JOIN usuarios u ON c.usuario = u.id
    WHERE c.id = curso_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerDetallesActividad` (IN `actividad_id` INT)   BEGIN
    SELECT 
        a.id, 
        a.titulo, 
        a.fechaf,
        u.nombre, 
        u.apellidos, 
        IFNULL(da.similitud, NULL) AS similitud, 
        IFNULL(da.id, NULL) AS codigo, 
        IFNULL(da.retraso, NULL) AS retraso, 
        IFNULL(da.nota, NULL) AS nota, 
        IFNULL(da.estado, 0) AS estado, 
        IFNULL(da.url, '') AS url 
    FROM 
        detallecurso dc 
    JOIN 
        actividades a ON dc.curso = a.curso 
    LEFT JOIN 
        detalleact da ON da.actividad = a.id AND da.alumno = dc.alumno 
    JOIN 
        usuarios u ON u.id = dc.alumno 
    WHERE 
        a.id=actividad_id
    GROUP BY 
        a.id, a.titulo, a.fechaf, u.id, u.nombre, u.apellidos, da.similitud, da.estado, da.url, da.retraso, da.nota, da.id 
ORDER BY da.similitud DESC;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id` int NOT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `fechaf` datetime NOT NULL,
  `fechai` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `curso` int NOT NULL,
  `estado` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades`
--

INSERT INTO `actividades` (`id`, `titulo`, `descripcion`, `fechaf`, `fechai`, `curso`, `estado`) VALUES
(1, 'Subir el codigo', 'Crear un programa para sumar 3 números', '2024-11-17 15:28:00', '2024-09-30', 3, 1),
(2, 'Prueba 10', 'esta es una prueba de actividad editando', '2024-10-15 15:32:00', '2024-09-30', 3, 1),
(3, 'prueba 2', 'esta es una prueba 2', '2024-10-01 15:33:00', '2024-09-30', 3, 1),
(4, 'Subir el codigo', 'Debes subir el codigo de un programa para restar 2 numeros', '2024-10-14 22:22:00', '2024-10-07', 5, 1),
(5, 'Subir el codigo', 'prueba de actividad', '2024-10-14 21:40:00', '2024-10-07', 6, 1),
(7, 'prueba 2', 'prueba', '2024-10-31 17:27:00', '2024-10-30', 7, 1),
(8, 'ejemplo validar', 'validar', '2024-11-26 10:19:00', '2024-10-30', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int NOT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `aula` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `usuario` int NOT NULL,
  `cod` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `estado` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `nombre`, `aula`, `usuario`, `cod`, `estado`) VALUES
(3, 'Desarrollo de videojuegos', '306a', 7, '923BA0', 1),
(4, 'Algoritmos 1', '306a', 7, 'B8FF6E', 1),
(5, 'Prueba 5', '306a', 7, '84E212', 1),
(6, 'prueba 23', '306a', 7, 'FB5DA4', 1),
(7, 'Mates 3', '305', 9, '3E87BE', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalleact`
--

CREATE TABLE `detalleact` (
  `id` int NOT NULL,
  `alumno` int NOT NULL,
  `url` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `similitud` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `actividad` int NOT NULL,
  `rutatxt` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `nota` int DEFAULT NULL,
  `retraso` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalleact`
--

INSERT INTO `detalleact` (`id`, `alumno`, `url`, `similitud`, `actividad`, `rutatxt`, `nota`, `retraso`, `estado`) VALUES
(1, 5, 'https://www.online-java.com/eX7GMClJRs', '93', 1, '../server/codigos/54ACE01120240930191311.txt', NULL, NULL, 1),
(2, 6, 'https://www.online-java.com/Sn6VaZ80Yv', '93', 1, '../server/codigos/64BAF61220241118135149.txt', 5, '22 h 23 m', 2),
(4, 6, 'https://www.online-java.com/etAKiyVbxr', '92', 5, '../server/codigos/6617154520241007214345.txt', NULL, NULL, 1),
(5, 5, 'https://www.online-java.com/zLomKjD4li', '92', 5, '../server/codigos/560E97A520241007214532.txt', NULL, NULL, 1),
(7, 8, 'https://www.online-java.com/Sn6VaZ80Yv', '84', 1, '../server/codigos/8083C3E120241104162641.txt', NULL, NULL, 1),
(8, 6, 'https://www.online-java.com/Sn6VaZ80Yv', NULL, 4, '../server/codigos/6B5A517420241104171553.txt', NULL, NULL, 1),
(9, 6, 'https://www.online-java.com/eX7GMClJRs', '0', 3, '../server/codigos/54ACE01120240930191311.txt', 20, NULL, 1),
(10, 6, 'https://www.online-java.com/eX7GMClJRs', NULL, 2, '../server/codigos/64B7147220241118135442.txt', NULL, '2 d 22 h 22 m', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallecurso`
--

CREATE TABLE `detallecurso` (
  `curso` int NOT NULL,
  `alumno` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detallecurso`
--

INSERT INTO `detallecurso` (`curso`, `alumno`) VALUES
(3, 6),
(5, 6),
(6, 6),
(6, 5),
(3, 5),
(4, 5),
(3, 8),
(4, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int NOT NULL,
  `fecha` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `noticia` text COLLATE utf8mb4_general_ci NOT NULL,
  `curso` int NOT NULL,
  `estado` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `fecha`, `noticia`, `curso`, `estado`) VALUES
(1, '2024-11-11', 'hola esto es una noticia hecha para el curso de desarrollo de videojuegos', 3, 0),
(2, '2024-11-11', 'esta es una prueba de noticia', 3, 1),
(3, '2024-11-11', 'Bienvenidos al curso de algoritmos', 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens`
--

CREATE TABLE `tokens` (
  `id` int NOT NULL,
  `idusuario` int NOT NULL,
  `token` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `estado` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tokens`
--

INSERT INTO `tokens` (`id`, `idusuario`, `token`, `fecha`, `estado`) VALUES
(3, 6, '7v3hi', '15-10-2024', 0),
(4, 6, '8uo3t', '15-10-2024', 0),
(5, 6, 'yuhxk', '15-10-2024', 0),
(6, 6, 'rnv1y', '15-10-2024', 0),
(7, 6, 't1xyq', '15-10-2024', 0),
(8, 6, 'df0yx', '01-11-2024', 0),
(9, 6, '0nz9r', '18-11-2024', 0),
(10, 6, 'il89s', '18-11-2024', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `correo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `clave` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellidos` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `rol` int NOT NULL,
  `estado` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `correo`, `clave`, `nombre`, `apellidos`, `rol`, `estado`) VALUES
(5, 'prueba@gmail.com', '$2y$10$OO5BwCQOuuxmL40k6Nbwxe1ImYr3d2hOzBwTp9hqcGuRFhLsHQe5G', 'Alumno', 'Prueba', 2, 1),
(6, 'alumno@gmail.com', '$2y$10$kvQQhoC5A6ovhohgkwxU4ujQE.mGHsPpj.vWDdsXmNkJ/O5toPo7K', 'Prueba2', 'prueba', 2, 1),
(7, 'profesor@gmail.com', '$2y$10$GEu9iPeFMQ4dySCn/nFFpOQVuzz0kjrriTVoZ4woxL5yNutvzud7m', 'Profesor', 'Prueba', 1, 1),
(8, 'julian@gmail.com', '$2y$10$JDzRROt6758Wp2QUsO1Tfec/sddS5dDqxs80hjAGM/GuWSUDicqtO', 'Julian', 'Mora', 2, 1),
(9, 'profesor2@gmail.com', '$2y$10$3FF522RC0tYZb/jws7JBNe1uAWOA7aD6ltd6/TBfSoh7QEhKPvBdy', 'Profesor', '2', 1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_curso` (`curso`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuario` (`usuario`);

--
-- Indices de la tabla `detalleact`
--
ALTER TABLE `detalleact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_actividades` (`actividad`),
  ADD KEY `fk_alumno` (`alumno`);

--
-- Indices de la tabla `detallecurso`
--
ALTER TABLE `detallecurso`
  ADD KEY `fk_alumnos` (`alumno`),
  ADD KEY `fk_cursos` (`curso`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_curso_noticia` (`curso`);

--
-- Indices de la tabla `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuario_token` (`idusuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalleact`
--
ALTER TABLE `detalleact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `fk_curso` FOREIGN KEY (`curso`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `detalleact`
--
ALTER TABLE `detalleact`
  ADD CONSTRAINT `fk_actividades` FOREIGN KEY (`actividad`) REFERENCES `actividades` (`id`),
  ADD CONSTRAINT `fk_alumno` FOREIGN KEY (`alumno`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `detallecurso`
--
ALTER TABLE `detallecurso`
  ADD CONSTRAINT `fk_alumnos` FOREIGN KEY (`alumno`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_cursos` FOREIGN KEY (`curso`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `fk_curso_noticia` FOREIGN KEY (`curso`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `fk_usuario_token` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
