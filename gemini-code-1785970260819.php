<?php
// public_html/base.php

$servidor   = 'localhost';
$base_datos = 'andyaxce_cine';     // Cambia por el nombre de tu base de datos
$usuario    = 'andyaxce_admin';    // Cambia por tu usuario MySQL
$clave      = 'TuContraseñaAqui';  // Cambia por tu clave MySQL

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8mb4", $usuario, $clave, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (\PDOException $e) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}
?>