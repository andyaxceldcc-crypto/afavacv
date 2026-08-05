<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/base.php';

try {
    $consulta = $pdo->prepare("SELECT id, title, category FROM channels WHERE active = 1 ORDER BY category, title");
    $consulta->execute();
    $canales = $consulta->fetchAll();

    echo json_encode(['success' => true, 'channels' => $canales]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener canales']);
}
?>