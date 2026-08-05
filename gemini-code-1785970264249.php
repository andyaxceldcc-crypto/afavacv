<?php
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

// CONEXIÓN A LA BASE DE DATOS EN ESPAÑOL
$servidor   = 'localhost';
$base_datos = 'andyaxce_cine';
$usuario    = 'andyaxce_admin';
$clave      = 'TuContraseñaAqui';

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8mb4", $usuario, $clave, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $consulta = $pdo->prepare("SELECT stream_url FROM channels WHERE id = ? AND active = 1");
    $consulta->execute([$id_canal]);
    $canal = $consulta->fetch();

    if (!$canal) {
        http_response_code(404);
        die("Canal no encontrado.");
    }

    header("Location: " . $canal['stream_url']);
    exit();
} catch (\PDOException $e) {
    die("Error en el servidor.");
}
?>