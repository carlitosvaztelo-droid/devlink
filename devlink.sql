-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para devlink
CREATE DATABASE IF NOT EXISTS `devlink` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `devlink`;

-- Volcando estructura para procedimiento devlink.ActualizarEstadisticasDesarrollador
DELIMITER //
CREATE PROCEDURE `ActualizarEstadisticasDesarrollador`(IN desarrollador_id INT)
BEGIN
    DECLARE total_proyectos INT;
    DECLARE proyectos_completados INT;
    DECLARE tasa_exito DECIMAL(5,2);
    
    -- Contar proyectos del desarrollador
    SELECT COUNT(*) INTO total_proyectos
    FROM contratos c
    WHERE c.desarrollador_id = desarrollador_id 
      AND c.estado IN ('activo', 'completado');
    
    -- Contar proyectos completados
    SELECT COUNT(*) INTO proyectos_completados
    FROM contratos c
    WHERE c.desarrollador_id = desarrollador_id 
      AND c.estado = 'completado';
    
    -- Calcular tasa de éxito
    IF total_proyectos > 0 THEN
        SET tasa_exito = (proyectos_completados / total_proyectos) * 100;
    ELSE
        SET tasa_exito = 0.00;
    END IF;
    
    -- Actualizar perfil
    UPDATE desarrolladores_perfiles 
    SET proyectos_completados = total_proyectos,
        tasa_exito = tasa_exito
    WHERE id = desarrollador_id;
END//
DELIMITER ;

-- Volcando estructura para tabla devlink.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `categoria_padre_id` int DEFAULT NULL,
  `activa` tinyint(1) DEFAULT '1',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `categoria_padre_id` (`categoria_padre_id`),
  KEY `idx_activa` (`activa`),
  CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`categoria_padre_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías de proyectos';

-- Volcando datos para la tabla devlink.categorias: ~4 rows (aproximadamente)
INSERT INTO `categorias` (`id`, `nombre`, `slug`, `descripcion`, `categoria_padre_id`, `activa`, `creado_en`) VALUES
	(1, 'Desarrollo Web', 'desarrollo-web', 'Proyectos de desarrollo web', NULL, 1, '2026-02-05 22:24:56'),
	(2, 'Diseño Gráfico', 'diseno-grafico', 'Diseño de logos, banners y material gráfico', NULL, 1, '2026-02-05 22:24:56'),
	(3, 'Marketing Digital', 'marketing-digital', 'Marketing online, SEO y redes sociales', NULL, 1, '2026-02-05 22:24:56'),
	(4, 'Aplicaciones Móviles', 'aplicaciones-moviles', 'Desarrollo de apps móviles', NULL, 1, '2026-02-05 22:24:56');

-- Volcando estructura para tabla devlink.clientes_perfiles
CREATE TABLE IF NOT EXISTS `clientes_perfiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `nombre_empresa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industria` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_empresa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto_preferido` enum('email','telefono','ambos') COLLATE utf8mb4_unicode_ci DEFAULT 'email',
  `descripcion_empresa` text COLLATE utf8mb4_unicode_ci,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  KEY `idx_ciudad` (`ciudad`),
  KEY `idx_industria` (`industria`),
  CONSTRAINT `clientes_perfiles_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Perfiles de clientes';

-- Volcando datos para la tabla devlink.clientes_perfiles: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.contratos
CREATE TABLE IF NOT EXISTS `contratos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `propuesta_id` int NOT NULL,
  `proyecto_id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `desarrollador_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `terminos` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `etapas_pago` int DEFAULT '4',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('borrador','pendiente_firma','activo','completado','cancelado','disputado') COLLATE utf8mb4_unicode_ci DEFAULT 'borrador',
  `firmado_cliente` tinyint(1) DEFAULT '0',
  `firmado_desarrollador` tinyint(1) DEFAULT '0',
  `firmado_en` datetime DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `propuesta_id` (`propuesta_id`),
  KEY `proyecto_id` (`proyecto_id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `desarrollador_id` (`desarrollador_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha_inicio` (`fecha_inicio`),
  CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`propuesta_id`) REFERENCES `propuestas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contratos_ibfk_2` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contratos_ibfk_3` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_perfiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contratos_ibfk_4` FOREIGN KEY (`desarrollador_id`) REFERENCES `desarrolladores_perfiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contratos';

-- Volcando datos para la tabla devlink.contratos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.conversaciones
CREATE TABLE IF NOT EXISTS `conversaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proyecto_id` int DEFAULT NULL,
  `propuesta_id` int DEFAULT NULL,
  `cliente_id` int NOT NULL,
  `desarrollador_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activa','archivada','cerrada') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `ultimo_mensaje` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `ultimo_mensaje_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unica_conversacion` (`cliente_id`,`desarrollador_id`,`proyecto_id`),
  KEY `proyecto_id` (`proyecto_id`),
  KEY `propuesta_id` (`propuesta_id`),
  KEY `desarrollador_id` (`desarrollador_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_ultimo_mensaje` (`ultimo_mensaje_en`),
  CONSTRAINT `conversaciones_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conversaciones_ibfk_2` FOREIGN KEY (`propuesta_id`) REFERENCES `propuestas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conversaciones_ibfk_3` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_perfiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversaciones_ibfk_4` FOREIGN KEY (`desarrollador_id`) REFERENCES `desarrolladores_perfiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Conversaciones';

-- Volcando datos para la tabla devlink.conversaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.desarrolladores_perfiles
CREATE TABLE IF NOT EXISTS `desarrolladores_perfiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `biografia` text COLLATE utf8mb4_unicode_ci,
  `enlace_portafolio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tarifa_hora` decimal(10,2) DEFAULT NULL,
  `disponibilidad` enum('disponible','tiempo_completo','medio_tiempo','no_disponible') COLLATE utf8mb4_unicode_ci DEFAULT 'disponible',
  `anios_experiencia` int DEFAULT '0',
  `especializaciones` text COLLATE utf8mb4_unicode_ci,
  `calificacion_promedio` decimal(3,2) DEFAULT '0.00',
  `proyectos_completados` int DEFAULT '0',
  `tasa_exito` decimal(5,2) DEFAULT '0.00',
  `ultimo_online` datetime DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  KEY `idx_tarifa` (`tarifa_hora`),
  KEY `idx_calificacion` (`calificacion_promedio`),
  KEY `idx_disponibilidad` (`disponibilidad`),
  CONSTRAINT `desarrolladores_perfiles_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Perfiles de desarrolladores';

-- Volcando datos para la tabla devlink.desarrolladores_perfiles: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.desarrollador_habilidades
CREATE TABLE IF NOT EXISTS `desarrollador_habilidades` (
  `desarrollador_id` int NOT NULL,
  `habilidad_id` int NOT NULL,
  `nivel` enum('principiante','intermedio','avanzado','experto') COLLATE utf8mb4_unicode_ci DEFAULT 'intermedio',
  `anios_experiencia` int DEFAULT '0',
  `es_principal` tinyint(1) DEFAULT '0',
  `agregado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`desarrollador_id`,`habilidad_id`),
  KEY `habilidad_id` (`habilidad_id`),
  KEY `idx_nivel` (`nivel`),
  CONSTRAINT `desarrollador_habilidades_ibfk_1` FOREIGN KEY (`desarrollador_id`) REFERENCES `desarrolladores_perfiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `desarrollador_habilidades_ibfk_2` FOREIGN KEY (`habilidad_id`) REFERENCES `habilidades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Habilidades de desarrolladores';

-- Volcando datos para la tabla devlink.desarrollador_habilidades: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.habilidades
CREATE TABLE IF NOT EXISTS `habilidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `idx_categoria` (`categoria`),
  FULLTEXT KEY `idx_busqueda` (`nombre`,`categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Habilidades técnicas';

-- Volcando datos para la tabla devlink.habilidades: ~10 rows (aproximadamente)
INSERT INTO `habilidades` (`id`, `nombre`, `categoria`, `descripcion`, `creado_en`) VALUES
	(1, 'PHP', 'Backend', 'Lenguaje de programación del lado del servidor', '2026-02-05 22:24:56'),
	(2, 'JavaScript', 'Frontend', 'Lenguaje para desarrollo web', '2026-02-05 22:24:56'),
	(3, 'HTML/CSS', 'Frontend', 'Lenguajes de marcado y estilos', '2026-02-05 22:24:56'),
	(4, 'MySQL', 'Base de Datos', 'Sistema de gestión de bases de datos', '2026-02-05 22:24:56'),
	(5, 'Laravel', 'Framework', 'Framework PHP', '2026-02-05 22:24:56'),
	(6, 'React', 'Frontend', 'Biblioteca JavaScript', '2026-02-05 22:24:56'),
	(7, 'Node.js', 'Backend', 'JavaScript del lado del servidor', '2026-02-05 22:24:56'),
	(8, 'Python', 'Backend', 'Lenguaje de programación general', '2026-02-05 22:24:56'),
	(9, 'Diseño UI/UX', 'Diseño', 'Diseño de interfaces y experiencia', '2026-02-05 22:24:56'),
	(10, 'WordPress', 'CMS', 'Sistema de gestión de contenidos', '2026-02-05 22:24:56');

-- Volcando estructura para tabla devlink.hitos
CREATE TABLE IF NOT EXISTS `hitos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proyecto_id` int NOT NULL,
  `contrato_id` int NOT NULL,
  `numero` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `monto` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('pendiente','en_progreso','completado','aprobado','rechazado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `fecha_completado` datetime DEFAULT NULL,
  `notas_cliente` text COLLATE utf8mb4_unicode_ci,
  `notas_desarrollador` text COLLATE utf8mb4_unicode_ci,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `proyecto_id` (`proyecto_id`),
  KEY `contrato_id` (`contrato_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_numero` (`numero`),
  CONSTRAINT `hitos_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hitos_ibfk_2` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hitos del proyecto';

-- Volcando datos para la tabla devlink.hitos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.mensajes
CREATE TABLE IF NOT EXISTS `mensajes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversacion_id` int NOT NULL,
  `remitente_id` int NOT NULL,
  `tipo_remitente` enum('cliente','desarrollador') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `archivo_ruta` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `archivo_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `archivo_tamano` int DEFAULT NULL,
  `leido` tinyint(1) DEFAULT '0',
  `leido_en` datetime DEFAULT NULL,
  `enviado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enviado` (`enviado_en`),
  KEY `idx_leido` (`leido`),
  KEY `idx_conversacion_fecha` (`conversacion_id`,`enviado_en`),
  CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`conversacion_id`) REFERENCES `conversaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mensajes individuales';

-- Volcando datos para la tabla devlink.mensajes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.notificaciones
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `tipo` enum('nueva_propuesta','propuesta_aceptada','nuevo_mensaje','nueva_reseña','actualizacion_proyecto','recordatorio_pago','actualizacion_hito','alerta_sistema') COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidad_id` int DEFAULT NULL,
  `tipo_entidad` enum('proyecto','propuesta','mensaje','reseña','pago','contrato','hito') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leida` tinyint(1) DEFAULT '0',
  `leida_en` datetime DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_leida` (`leida`),
  KEY `idx_creado` (`creado_en`),
  KEY `idx_tipo` (`tipo`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notificaciones';

-- Volcando datos para la tabla devlink.notificaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.propuestas
CREATE TABLE IF NOT EXISTS `propuestas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proyecto_id` int NOT NULL,
  `desarrollador_id` int NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `presupuesto` decimal(10,2) NOT NULL,
  `tiempo_estimado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion_hitos` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('pendiente','aceptada','rechazada','cancelada','retirada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `leida_cliente` tinyint(1) DEFAULT '0',
  `enviada_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizada_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `respondida_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_proyecto_desarrollador` (`proyecto_id`,`desarrollador_id`),
  KEY `desarrollador_id` (`desarrollador_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_enviada` (`enviada_en`),
  CONSTRAINT `propuestas_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `propuestas_ibfk_2` FOREIGN KEY (`desarrollador_id`) REFERENCES `desarrolladores_perfiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Propuestas de desarrolladores';

-- Volcando datos para la tabla devlink.propuestas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.proyectos
CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int DEFAULT NULL,
  `presupuesto_min` decimal(10,2) NOT NULL,
  `presupuesto_max` decimal(10,2) NOT NULL,
  `duracion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel_ingles` enum('no_requerido','basico','intermedio','avanzado','nativo') COLLATE utf8mb4_unicode_ci DEFAULT 'no_requerido',
  `estado` enum('borrador','abierto','en_progreso','completado','cancelado','cerrado') COLLATE utf8mb4_unicode_ci DEFAULT 'abierto',
  `visibilidad` enum('publico','privado') COLLATE utf8mb4_unicode_ci DEFAULT 'publico',
  `vistas` int DEFAULT '0',
  `propuestas` int DEFAULT '0',
  `propuesta_seleccionada_id` int DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_presupuesto` (`presupuesto_min`,`presupuesto_max`),
  KEY `idx_creado` (`creado_en`),
  FULLTEXT KEY `idx_busqueda` (`titulo`,`descripcion`),
  CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes_perfiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `proyectos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Proyectos publicados';

-- Volcando datos para la tabla devlink.proyectos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.proyectos_guardados
CREATE TABLE IF NOT EXISTS `proyectos_guardados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `proyecto_id` int NOT NULL,
  `guardado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_guardado` (`usuario_id`,`proyecto_id`),
  KEY `proyecto_id` (`proyecto_id`),
  CONSTRAINT `proyectos_guardados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `proyectos_guardados_ibfk_2` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Proyectos guardados';

-- Volcando datos para la tabla devlink.proyectos_guardados: ~0 rows (aproximadamente)

-- Volcando estructura para función devlink.PuedeEnviarPropuesta
DELIMITER //
CREATE FUNCTION `PuedeEnviarPropuesta`(desarrollador_id INT, proyecto_id INT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE conteo_propuestas INT;
    DECLARE max_propuestas INT DEFAULT 5;
    
    SELECT COUNT(*) INTO conteo_propuestas
    FROM propuestas
    WHERE desarrollador_id = desarrollador_id
      AND proyecto_id = proyecto_id;
    
    RETURN conteo_propuestas = 0;
END//
DELIMITER ;

-- Volcando estructura para tabla devlink.reseñas
CREATE TABLE IF NOT EXISTS `reseñas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proyecto_id` int NOT NULL,
  `contrato_id` int DEFAULT NULL,
  `reseñador_id` int NOT NULL,
  `usuario_reseñado_id` int NOT NULL,
  `tipo_reseñador` enum('cliente','desarrollador') COLLATE utf8mb4_unicode_ci NOT NULL,
  `calificacion` decimal(2,1) NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `respuesta` text COLLATE utf8mb4_unicode_ci,
  `fecha_respuesta` datetime DEFAULT NULL,
  `publica` tinyint(1) DEFAULT '1',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unica_reseña` (`proyecto_id`,`reseñador_id`,`usuario_reseñado_id`),
  KEY `contrato_id` (`contrato_id`),
  KEY `reseñador_id` (`reseñador_id`),
  KEY `usuario_reseñado_id` (`usuario_reseñado_id`),
  KEY `idx_calificacion` (`calificacion`),
  KEY `idx_creado` (`creado_en`),
  CONSTRAINT `reseñas_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reseñas_ibfk_2` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reseñas_ibfk_3` FOREIGN KEY (`reseñador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reseñas_ibfk_4` FOREIGN KEY (`usuario_reseñado_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reseñas de usuarios';

-- Volcando datos para la tabla devlink.reseñas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.restablecimiento_contraseñas
CREATE TABLE IF NOT EXISTS `restablecimiento_contraseñas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado_en` datetime DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_expira` (`expira_en`),
  CONSTRAINT `restablecimiento_contraseñas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recuperación de contraseñas';

-- Volcando datos para la tabla devlink.restablecimiento_contraseñas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla devlink.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` enum('cliente','desarrollador') COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contraseña` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagen_perfil` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `ultimo_login` datetime DEFAULT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cedula` (`cedula`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_email` (`email`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuarios del sistema';

-- Volcando datos para la tabla devlink.usuarios: ~0 rows (aproximadamente)

-- Volcando estructura para vista devlink.vista_mejores_desarrolladores
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_mejores_desarrolladores` (
	`id` INT NOT NULL,
	`tarifa_hora` DECIMAL(10,2) NULL,
	`calificacion_promedio` DECIMAL(3,2) NULL,
	`proyectos_completados` INT NULL,
	`tasa_exito` DECIMAL(5,2) NULL,
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`apellido` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`email` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`habilidades` TEXT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Volcando estructura para vista devlink.vista_proyectos_activos
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vista_proyectos_activos` (
	`id` INT NOT NULL,
	`titulo` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`categoria` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`presupuesto_min` DECIMAL(10,2) NOT NULL,
	`presupuesto_max` DECIMAL(10,2) NOT NULL,
	`estado` ENUM('borrador','abierto','en_progreso','completado','cancelado','cerrado') NULL COLLATE 'utf8mb4_unicode_ci',
	`propuestas` INT NULL,
	`vistas` INT NULL,
	`creado_en` DATETIME NULL,
	`nombre_empresa` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`cliente_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`cliente_apellido` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci'
) ENGINE=MyISAM;

-- Volcando estructura para disparador devlink.actualizar_calificacion_desarrollador
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `actualizar_calificacion_desarrollador` AFTER INSERT ON `reseñas` FOR EACH ROW BEGIN
    DECLARE promedio DECIMAL(3,2);
    
    -- Solo si el reseñado es un desarrollador
    IF (SELECT tipo FROM usuarios WHERE id = NEW.usuario_reseñado_id) = 'desarrollador' THEN
        SELECT AVG(calificacion) INTO promedio 
        FROM reseñas 
        WHERE usuario_reseñado_id = NEW.usuario_reseñado_id;
        
        UPDATE desarrolladores_perfiles dp
        JOIN usuarios u ON dp.usuario_id = u.id
        SET dp.calificacion_promedio = COALESCE(promedio, 0.00)
        WHERE u.id = NEW.usuario_reseñado_id;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador devlink.aumentar_contador_propuestas
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `aumentar_contador_propuestas` AFTER INSERT ON `propuestas` FOR EACH ROW BEGIN
    UPDATE proyectos 
    SET propuestas = propuestas + 1 
    WHERE id = NEW.proyecto_id;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador devlink.disminuir_contador_propuestas
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `disminuir_contador_propuestas` AFTER DELETE ON `propuestas` FOR EACH ROW BEGIN
    UPDATE proyectos 
    SET propuestas = propuestas - 1 
    WHERE id = OLD.proyecto_id;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_mejores_desarrolladores`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_mejores_desarrolladores` AS select `dp`.`id` AS `id`,`dp`.`tarifa_hora` AS `tarifa_hora`,`dp`.`calificacion_promedio` AS `calificacion_promedio`,`dp`.`proyectos_completados` AS `proyectos_completados`,`dp`.`tasa_exito` AS `tasa_exito`,`u`.`nombre` AS `nombre`,`u`.`apellido` AS `apellido`,`u`.`email` AS `email`,group_concat(distinct `h`.`nombre` separator ', ') AS `habilidades` from (((`desarrolladores_perfiles` `dp` join `usuarios` `u` on((`dp`.`usuario_id` = `u`.`id`))) left join `desarrollador_habilidades` `dh` on((`dp`.`id` = `dh`.`desarrollador_id`))) left join `habilidades` `h` on((`dh`.`habilidad_id` = `h`.`id`))) where (`dp`.`calificacion_promedio` >= 4.0) group by `dp`.`id` order by `dp`.`calificacion_promedio` desc,`dp`.`proyectos_completados` desc;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vista_proyectos_activos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vista_proyectos_activos` AS select `p`.`id` AS `id`,`p`.`titulo` AS `titulo`,`c`.`nombre` AS `categoria`,`p`.`presupuesto_min` AS `presupuesto_min`,`p`.`presupuesto_max` AS `presupuesto_max`,`p`.`estado` AS `estado`,`p`.`propuestas` AS `propuestas`,`p`.`vistas` AS `vistas`,`p`.`creado_en` AS `creado_en`,`cp`.`nombre_empresa` AS `nombre_empresa`,`u`.`nombre` AS `cliente_nombre`,`u`.`apellido` AS `cliente_apellido` from (((`proyectos` `p` join `clientes_perfiles` `cp` on((`p`.`cliente_id` = `cp`.`id`))) join `usuarios` `u` on((`cp`.`usuario_id` = `u`.`id`))) left join `categorias` `c` on((`p`.`categoria_id` = `c`.`id`))) where (`p`.`estado` in ('abierto','en_progreso'));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
