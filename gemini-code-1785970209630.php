<?php
$servidor   = 'localhost';
$base_datos = 'andyaxce_cine';     -- El nombre de la base de datos que creaste en tu phpMyAdmin local
$usuario    = 'root';              -- En XAMPP por defecto es root
$clave      = '';                  -- En XAMPP por defecto la contraseña va vacía

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8mb4", $usuario, $clave, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (\PDOException $e) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos local']);
    exit;
}
?>