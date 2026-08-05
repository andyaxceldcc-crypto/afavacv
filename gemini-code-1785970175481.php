<?php
$servidor   = 'localhost';
$base_datos = 'andyaxce_cine';  // Cambia esto en cPanel a tu_base
$usuario    = 'root';           // Cambia esto en cPanel a tu_usuario
$clave      = '';               // Cambia esto en cPanel a tu_clave

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8mb4", $usuario, $clave, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (\PDOException $e) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la BD']);
    exit;
}
?>