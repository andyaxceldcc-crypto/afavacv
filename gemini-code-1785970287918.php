<?php
session_start();
require_once 'db.php';

// 1. Validar que el usuario haya pagado y tenga sesión activa
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die("Acceso Denegado: Debes iniciar sesión para ver el contenido.");
}

$channel_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$channel_id) {
    http_response_code(400);
    die("ID de canal no válido.");
}

// 2. Obtener la URL real alojada en la base de datos MySQL
$stmt = $pdo->prepare("SELECT stream_url FROM channels WHERE id = ? AND active = 1");
$stmt->execute([$channel_id]);
$channel = $stmt->fetch();

if (!$channel) {
    http_response_code(404);
    die("Canal no encontrado.");
}

$real_url = $channel['stream_url'];

// 3. Redirección segura mediante encabezado HTTP
header("Location: " . $real_url);
exit();
?>