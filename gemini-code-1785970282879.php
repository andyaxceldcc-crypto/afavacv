<?php
session_start();
header("Content-Type: application/json");
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

try {
    // Solo seleccionamos id, title y category. OMITIMOS stream_url por seguridad.
    $stmt = $pdo->prepare("SELECT id, title, category FROM channels WHERE active = 1 ORDER BY category, title");
    $stmt->execute();
    $channels = $stmt->fetchAll();

    echo json_encode(['success' => true, 'channels' => $channels]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener canales']);
}
?>