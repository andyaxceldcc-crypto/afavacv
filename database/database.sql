-- Video Streaming Platform Database Schema
-- Created: 2026
-- Version: 1.0

CREATE DATABASE IF NOT EXISTS streaming_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE streaming_db;

-- Tabla: usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
);

-- Tabla: categorias
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT DEFAULT NULL,
    icono VARCHAR(50) DEFAULT NULL,
    color VARCHAR(7) DEFAULT '#FF6B6B',
    orden INT DEFAULT 0,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_activa (activa)
);

-- Tabla: videos
CREATE TABLE videos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion LONGTEXT DEFAULT NULL,
    categoria_id INT NOT NULL,
    archivo_video VARCHAR(255) NOT NULL,
    miniatura VARCHAR(255) DEFAULT NULL,
    duracion INT DEFAULT 0,
    vistas INT DEFAULT 0,
    likes INT DEFAULT 0,
    dislikes INT DEFAULT 0,
    comentarios_count INT DEFAULT 0,
    premium BOOLEAN DEFAULT FALSE,
    estado ENUM('borrador', 'publicado', 'eliminado') DEFAULT 'borrador',
    fecha_publicacion TIMESTAMP DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_estado (estado),
    INDEX idx_premium (premium),
    FULLTEXT idx_busqueda (titulo, descripcion)
);

-- Tabla: videos_relacionados
CREATE TABLE videos_relacionados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    video_id INT NOT NULL,
    video_relacionado_id INT NOT NULL,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (video_relacionado_id) REFERENCES videos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_videos (video_id, video_relacionado_id)
);

-- Tabla: comentarios
CREATE TABLE comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    video_id INT NOT NULL,
    usuario_id INT NOT NULL,
    contenido TEXT NOT NULL,
    likes INT DEFAULT 0,
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_video (video_id),
    INDEX idx_usuario (usuario_id)
);

-- Tabla: suscripciones
CREATE TABLE suscripciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    canal_usuario_id INT NOT NULL,
    fecha_suscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (canal_usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uk_suscripcion (usuario_id, canal_usuario_id)
);

-- Tabla: planes_premium
CREATE TABLE planes_premium (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    duracion_dias INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    caracteristicas JSON DEFAULT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
);

-- Tabla: suscripciones_premium
CREATE TABLE suscripciones_premium (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    plan_id INT NOT NULL,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento TIMESTAMP NOT NULL,
    estado ENUM('activa', 'vencida', 'cancelada') DEFAULT 'activa',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES planes_premium(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
);

-- Tabla: pagos
CREATE TABLE pagos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    suscripcion_premium_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'USD',
    metodo_pago VARCHAR(50) NOT NULL,
    proveedor VARCHAR(50) NOT NULL,
    id_transaccion VARCHAR(255) UNIQUE NOT NULL,
    estado ENUM('pendiente', 'completado', 'fallido', 'cancelado') DEFAULT 'pendiente',
    descripcion TEXT DEFAULT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (suscripcion_premium_id) REFERENCES suscripciones_premium(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_transaccion (id_transaccion)
);

-- Tabla: reproducciones_historial
CREATE TABLE reproducciones_historial (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    video_id INT NOT NULL,
    tiempo_reproduccion INT DEFAULT 0,
    fecha_reproduccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_video (video_id)
);

-- Tabla: favoritos
CREATE TABLE favoritos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    video_id INT NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_favorito (usuario_id, video_id)
);

-- Tabla: configuracion
CREATE TABLE configuracion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor LONGTEXT DEFAULT NULL,
    tipo ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    descripcion TEXT DEFAULT NULL,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clave (clave)
);

-- Tabla: notificaciones
CREATE TABLE notificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT DEFAULT NULL,
    datos JSON DEFAULT NULL,
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_leida (leida)
);

-- Tabla: registros_actividad
CREATE TABLE registros_actividad (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT DEFAULT NULL,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (fecha_creacion)
);

-- Insertar categorías por defecto
INSERT INTO categorias (nombre, descripcion, icono, color) VALUES
('Películas', 'Películas y largometrajes', 'film', '#FF6B6B'),
('Series', 'Series de TV y episodios', 'tv', '#4ECDC4'),
('Documentales', 'Documentales educativos', 'book', '#45B7D1'),
('Música', 'Videos musicales y conciertos', 'music', '#FFA502'),
('Deportes', 'Contenido deportivo', 'trophy', '#E91E63'),
('Educación', 'Contenido educativo', 'graduation', '#9C27B0'),
('Gaming', 'Videos de videojuegos', 'gamepad', '#00BCD4'),
('Lifestyle', 'Estilo de vida y viajes', 'heart', '#FF1744');

-- Insertar planes premium por defecto
INSERT INTO planes_premium (nombre, descripcion, duracion_dias, precio, caracteristicas) VALUES
('Mensual', 'Acceso premium por 1 mes', 30, 9.99, '{"ads": false, "calidad": "4K", "descargas": 5}'),
('Trimestral', 'Acceso premium por 3 meses', 90, 24.99, '{"ads": false, "calidad": "4K", "descargas": 15}'),
('Anual', 'Acceso premium por 1 año', 365, 79.99, '{"ads": false, "calidad": "4K", "descargas": 100}');

-- Insertar configuración por defecto
INSERT INTO configuracion (clave, valor, tipo, descripcion) VALUES
('nombre_sitio', 'StreamingPro', 'string', 'Nombre del sitio web'),
('logo_url', '/assets/img/logo.png', 'string', 'URL del logo'),
('favicon_url', '/assets/img/favicon.ico', 'string', 'URL del favicon'),
('descripcion_sitio', 'Plataforma de streaming de videos profesional', 'string', 'Descripción del sitio'),
('email_contacto', 'contacto@streaming.com', 'string', 'Email de contacto'),
('videos_por_pagina', '12', 'integer', 'Cantidad de videos por página'),
('max_upload_size', '5368709120', 'integer', 'Tamaño máximo de carga en bytes (5GB)'),
('jwt_secret', 'your-secret-key-change-this', 'string', 'Clave secreta JWT'),
('modo_mantenimiento', 'false', 'boolean', 'Activar modo mantenimiento');