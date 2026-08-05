<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
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

    $consulta = $pdo->prepare("SELECT id, title, category FROM channels WHERE active = 1 ORDER BY category, title");
    $consulta->execute();
    $lista_canales = $consulta->fetchAll();

    echo json_encode(['success' => true, 'channels' => $lista_canales]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener canales']);
}
?>