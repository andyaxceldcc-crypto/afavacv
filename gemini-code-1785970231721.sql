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

CREATE TABLE IF NOT EXISTS `channels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `stream_url` TEXT NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'General',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `channels` (`title`, `category`, `stream_url`, `active`) VALUES
('RTVA Live HD', 'En Vivo', 'https://livesg1.rtva.hiway.media/11a6d6f4-ee13-47c7-9c27-7313cf5424e2/manifest.m3u8', 1),
('Demo Streaming HLS', 'Películas', 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8', 1);