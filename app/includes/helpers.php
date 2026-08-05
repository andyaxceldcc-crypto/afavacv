<?php
/**
 * Funciones auxiliares globales
 */

/**
 * Redirigir a una URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Obtener valor de GET/POST de forma segura
 */
function getInput($key, $default = '', $sanitize = true) {
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    
    if ($sanitize && $value !== '') {
        $value = \App\Includes\Security::sanitize($value);
    }
    
    return $value;
}

/**
 * Escapar salida HTML
 */
function escape($data) {
    return \App\Includes\Security::escape($data);
}

/**
 * Obtener configuración
 */
function getConfig($key, $default = null) {
    static $config = [];
    
    if (empty($config)) {
        $db = \App\Config\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT clave, valor FROM configuracion");
        $stmt->execute();
        
        foreach ($stmt->fetchAll() as $row) {
            $config[$row['clave']] = $row['valor'];
        }
    }
    
    return $config[$key] ?? $default;
}

/**
 * Establecer configuración
 */
function setConfig($key, $value) {
    $db = \App\Config\Database::getInstance()->getConnection();
    $stmt = $db->prepare(
        "INSERT INTO configuracion (clave, valor) VALUES (?, ?) 
         ON DUPLICATE KEY UPDATE valor = ?"
    );
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Registrar error o mensaje
 */
function setFlash($key, $message, $type = 'info') {
    $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
}

/**
 * Obtener y limpiar mensaje flash
 */
function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}

/**
 * Formatear tamaño de archivo
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Formatear duración de video
 */
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Formatear moneda
 */
function formatCurrency($amount, $currency = 'USD') {
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'PEN' => 'S/',
        'MXN' => '$',
        'BRL' => 'R$'
    ];
    
    $symbol = $symbols[$currency] ?? $currency;
    return $symbol . ' ' . number_format($amount, 2, '.', ',');
}

/**
 * Generar slug
 */
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Tiempo transcurrido
 */
function timeAgo($date) {
    $time = strtotime($date);
    $diff = time() - $time;
    
    if ($diff < 60) return 'hace unos segundos';
    elseif ($diff < 3600) return 'hace ' . floor($diff / 60) . ' minutos';
    elseif ($diff < 86400) return 'hace ' . floor($diff / 3600) . ' horas';
    elseif ($diff < 604800) return 'hace ' . floor($diff / 86400) . ' días';
    else return 'hace ' . floor($diff / 604800) . ' semanas';
}
