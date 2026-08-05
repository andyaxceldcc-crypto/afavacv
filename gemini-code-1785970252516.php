<?php
// public_html/stream.php
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die("Acceso Denegado: Debes iniciar sesión.");
}

$id_canal = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_canal) {
    http_response_code(400);
    die("ID no válido.");
}

// Incluimos la conexión central
require_once 'base.php';

try {
    $consulta = $pdo->prepare("SELECT stream_url FROM channels WHERE id = ? AND active = 1");
    $consulta->execute([$id_canal]);
    $canal = $consulta->fetch();

    if (!$canal) {
        http_response_code(404);
        die("Canal no encontrado.");
    }

    // Redirige al stream original sin mostrar la URL real en el HTML
    header("Location: " . $canal['stream_url']);
    exit();
} catch (\PDOException $e) {
    die("Error en el servidor.");
}
?>