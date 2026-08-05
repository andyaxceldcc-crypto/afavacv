<?php
// api/proxy.php
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die("Acceso denegado. Debes iniciar sesión.");
}

$id_canal = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_canal) {
    http_response_code(400);
    die("ID de canal inválido.");
}

require_once __DIR__ . '/../config/base.php';

try {
    $consulta = $pdo->prepare("SELECT stream_url FROM channels WHERE id = ? AND active = 1");
    $consulta->execute([$id_canal]);
    $canal = $consulta->fetch();

    if (!$canal) {
        http_response_code(404);
        die("Canal no encontrado.");
    }

    $url_original = $canal['stream_url'];

    // Proxy para ocultar la fuente original leyendo la transmisión
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_original);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    
    $contenido = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        header("Content-Type: application/vnd.apple.mpegurl");
        echo $contenido;
    } else {
        // Redirección de respaldo si no soporta streaming directo por cURL
        header("Location: " . $url_original);
    }
    exit();

} catch (\PDOException $e) {
    http_response_code(500);
    die("Error interno en el servidor.");
}
?>