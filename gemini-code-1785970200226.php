<?php
// api/proxy.php
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die("Acceso Denegado");
}

$id_canal = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// AQUÍ AGREGAS TODOS TUS ENLACES DE VIDEO O CANALES IPTV
$lista_canales = [
    1 => 'https://livesg1.rtva.hiway.media/11a6d6f4-ee13-47c7-9c27-7313cf5424e2/manifest.m3u8',
    2 => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
    3 => 'https://bitdash-a.akamaihd.net/content/sintel/hls/playlist.m3u8',
    4 => 'AQUí_PEGA_TU_OTRO_ENLACE_M3U8_O_PELICULA',
    5 => 'AQUí_PEGA_OTRO_MAS'
];

if (!$id_canal || !isset($lista_canales[$id_canal])) {
    http_response_code(404);
    die("Canal no disponible");
}

header("Location: " . $lista_canales[$id_canal]);
exit();
?>