<?php
// public_html/get-channels.php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

// Incluimos la conexión central
require_once 'base.php';

try {
    $consulta = $pdo->prepare("SELECT id, title, category FROM channels WHERE active = 1 ORDER BY category, title");
    $consulta->execute();
    $lista_canales = $consulta->fetchAll();

    echo json_encode(['success' => true, 'channels' => $lista_canales]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener canales']);
}
?>