<?php
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die("Acceso Denegado");
}

$id_canal = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Aquí pegas todos tus enlaces reales m3u8 o streams
$lista_canales = [
    1 => 'https://livesg1.rtva.hiway.media/11a6d6f4-ee13-47c7-9c27-7313cf5424e2/manifest.m3u8',
    2 => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
    3 => 'https://bitdash-a.akamaihd.net/content/sintel/hls/playlist.m3u8',
];

if (!$id_canal || !isset($lista_canales[$id_canal])) {
    http_response_code(404);
    die("Canal no disponible");
}

header("Location: " . $lista_canales[$id_canal]);
exit();
?>