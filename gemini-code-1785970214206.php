<?php
// api/proxy.php
session_start();

// 1. Validar que el usuario haya pagado e iniciado sesión
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die("Acceso Denegado");
}

$id_canal = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// 2. Mapeo de Canales IPTV (Agrega aquí tus miles de enlaces m3u8)
$lista_canales = [
    1 => 'https://livesg1.rtva.hiway.media/11a6d6f4-ee13-47c7-9c27-7313cf5424e2/manifest.m3u8',
    2 => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
    3 => 'https://bitdash-a.akamaihd.net/content/sintel/hls/playlist.m3u8',
    // ... agrega más IDs y enlaces aquí
];

if (!$id_canal || !isset($lista_canales[$id_canal])) {
    http_response_code(404);
    die("Canal no disponible");
}

$stream_url = $lista_canales[$id_canal];

// 3. Ocultar la URL redirigiendo la emisión internamente
header("Location: " . $stream_url);
exit();
?>