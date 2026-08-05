<?php
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die("Acceso Denegado: Debes iniciar sesión.");
}

$channel_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$channel_id) {
    http_response_code(400);
    die("ID no válido.");
}

// CONEXIÓN DIRECTA A MYSQL
$host = 'localhost';
$db   = 'andyaxce_cine';
$user = 'andyaxce_admin';
$pass = 'TuContraseñaAqui';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->prepare("SELECT stream_url FROM channels WHERE id = ? AND active = 1");
    $stmt->execute([$channel_id]);
    $channel = $stmt->fetch();

    if (!$channel) {
        http_response_code(404);
        die("Canal no encontrado.");
    }

    header("Location: " . $channel['stream_url']);
    exit();
} catch (\PDOException $e) {
    die("Error en el servidor.");
}
?>