<?php
// api/get-channels.php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Los nombres que aparecerán en tu reproductor web (deben coincidir con los IDs del proxy.php)
$canales = [
    ['id' => 1, 'title' => 'Canal 1: RTVA Live', 'category' => 'En Vivo'],
    ['id' => 2, 'title' => 'Canal 2: Demo Mux', 'category' => 'Películas'],
    ['id' => 3, 'title' => 'Película Sintel', 'category' => 'Cine'],
    ['id' => 4, 'title' => 'Película o Canal 4', 'category' => 'Estrenos'],
    ['id' => 5, 'title' => 'Película o Canal 5', 'category' => 'Deportes'],
];

echo json_encode(['success' => true, 'channels' => $canales]);
?>