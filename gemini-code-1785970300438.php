<?php
session_start();
header("Content-Type: application/json");
require_once 'db.php';

// Verificar que el usuario esté logueado y activo
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

// Obtener solo canales activos
$stmt = $pdo->prepare("SELECT id, title, category, stream_url, logo_url FROM channels WHERE active = 1 ORDER BY category, title");
$stmt->execute();
$channels = $stmt->fetchAll();

echo json_encode(['success' => true, 'channels' => $channels]);
?>