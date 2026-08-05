-- 1. TABLA PARA USUARIOS QUE PAGAN (Yape / Stripe)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `phone` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `method` ENUM('yape', 'stripe') NOT NULL DEFAULT 'yape',
  `lang` ENUM('es', 'en') NOT NULL DEFAULT 'es',
  `payment_ref` VARCHAR(100) NULL,
  `stripe_payment_id` VARCHAR(100) NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABLA PARA TUS CIENTOS DE ENLACES M3U8 DE CANALES
CREATE TABLE IF NOT EXISTS `channels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) DEFAULT 'General',
  `stream_url` TEXT NOT NULL,
  `logo_url` VARCHAR(255) NULL,
  `active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. EJEMPLO DE CANALES INICIALES (Agrega las URL que tengas)
INSERT INTO `channels` (`title`, `category`, `stream_url`) VALUES
('RTVA Live HD', 'En Vivo', 'https://livesg1.rtva.hiway.media/11a6d6f4-ee13-47c7-9c27-7313cf5424e2/manifest.m3u8'),
('Canal Deportes 1', 'Deportes', 'http://190.93.224.42/LIGA-1-MAX/index.m3u8');