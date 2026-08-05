<?php
// api/get-channels.php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Lista de Canales para el Menú
$canales = [
    ['id' => 1, 'title' => 'RTVA Live HD', 'category' => 'En Vivo'],
    ['id' => 2, 'title' => 'Canal Películas 1', 'category' => 'Cine'],
    ['id' => 3, 'title' => 'Canal Deportes', 'category' => 'Deportes'],
];

echo json_encode(['success' => true, 'channels' => $canales]);
?>