<?php
/**
 * Configuración Principal - StreamingPro
 * Variables de entorno y configuraciones globales
 */

// Configuración de zona horaria
date_default_timezone_set('America/Lima');

// Modo Debug
define('DEBUG_MODE', true);

// Rutas del proyecto
define('BASE_PATH', dirname(dirname(__FILE__)));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('VIEWS_PATH', APP_PATH . '/views');
define('MODELS_PATH', APP_PATH . '/models');
define('CONTROLLERS_PATH', APP_PATH . '/controllers');
define('INCLUDES_PATH', APP_PATH . '/includes');

// URL Base
define('BASE_URL', 'http://localhost:8000');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Configuración de Base de Datos
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'streaming_db');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);

// Configuración de Seguridad
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-in-production');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 86400); // 24 horas
define('CSRF_TOKEN_LENGTH', 32);

// Configuración de Sesión
define('SESSION_NAME', 'streaming_session');
define('SESSION_LIFETIME', 86400); // 24 horas
define('SESSION_PATH', '/') ;
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', false); // true en producción con HTTPS
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// Configuración de Archivos
define('MAX_UPLOAD_SIZE', 5368709120); // 5GB en bytes
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm', 'video/ogg']);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['mp4', 'webm', 'ogg', 'jpg', 'jpeg', 'png', 'webp']);

// Configuración de Paginación
define('ITEMS_PER_PAGE', 12);
define('MAX_ITEMS_PER_PAGE', 100);

// Configuración de Email
define('MAIL_FROM', 'noreply@streaming.com');
define('MAIL_FROM_NAME', 'StreamingPro');
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');

// Configuración de Pagos
define('STRIPE_PUBLIC_KEY', $_ENV['STRIPE_PUBLIC_KEY'] ?? '');
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('PAYPAL_CLIENT_ID', $_ENV['PAYPAL_CLIENT_ID'] ?? '');
define('PAYPAL_SECRET', $_ENV['PAYPAL_SECRET'] ?? '');
define('CULQI_PUBLIC_KEY', $_ENV['CULQI_PUBLIC_KEY'] ?? '');
define('CULQI_SECRET_KEY', $_ENV['CULQI_SECRET_KEY'] ?? '');

// Configuración de Logs
define('LOG_PATH', BASE_PATH . '/logs');
define('LOG_LEVEL', 'info'); // debug, info, warning, error

// Headers de Seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Crear directorios necesarios si no existen
$directories = [
    UPLOADS_PATH,
    UPLOADS_PATH . '/videos',
    UPLOADS_PATH . '/thumbnails',
    UPLOADS_PATH . '/profiles',
    LOG_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => SESSION_PATH,
        'domain' => SESSION_DOMAIN,
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => SESSION_SAMESITE
    ]);
    session_start();
}

// Establecer locale
setlocale(LC_TIME, 'es_PE.UTF-8');
setlocale(LC_MONETARY, 'es_PE.UTF-8');
