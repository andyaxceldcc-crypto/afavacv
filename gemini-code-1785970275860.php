<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
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

    $stmt = $pdo->prepare("SELECT id, title, category FROM channels WHERE active = 1 ORDER BY category, title");
    $stmt->execute();
    $channels = $stmt->fetchAll();

    echo json_encode(['success' => true, 'channels' => $channels]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener canales']);
}
?>