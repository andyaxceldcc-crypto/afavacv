-- 1. Crear las tablas si no existen

-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(25) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `method` ENUM('stripe', 'yape') NOT NULL DEFAULT 'yape',
  `lang` VARCHAR(5) NOT NULL DEFAULT 'es',
  `payment_ref` VARCHAR(100) DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Categorías de Canales
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Canales/Películas
CREATE TABLE IF NOT EXISTS `channels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `stream_url` TEXT NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'General',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insertar categorías de prueba
INSERT INTO `categories` (`name`) VALUES
('Películas'),
('Deportes'),
('Entretenimiento'),
('Noticias');

-- 3. Insertar canales de prueba
INSERT INTO `channels` (`title`, `stream_url`, `category`, `active`) VALUES
('Canal Demo 1 (m3u8)', 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8', 'Películas', 1),
('Canal Demo 2 (m3u8)', 'https://bitdash-a.akamaihd.net/content/sintel/hls/playlist.m3u8', 'Entretenimiento', 1);