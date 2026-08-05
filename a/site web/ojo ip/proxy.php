<?php
// proxy.php - Reescribe listas M3U8 y resuelve rutas relativas
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: *");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!isset($_GET['url'])) {
    http_response_code(400);
    echo "Falta el parámetro URL";
    exit;
}

$url = $_GET['url'];

// Obtener la URL base del stream original para resolver rutas relativas
$urlParts = parse_url($url);
$baseUrl = $urlParts['scheme'] . '://' . $urlParts['host'];
if (isset($urlParts['path']) && strlen($urlParts['path']) > 0) {
    $dir = dirname($urlParts['path']);
    $baseUrl .= ($dir === '/' || $dir === '\\') ? '/' : $dir . '/';
} else {
    $baseUrl .= '/';
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

if (strpos($url, 'cloudfront.net') !== false || strpos($url, 'wurl.tv') !== false) {
    curl_setopt($ch, CURLOPT_REFERER, 'https://www.google.com/');
}

$data = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || $data === false) {
    http_response_code($httpCode ? $httpCode : 500);
    echo "Error al conectar con la señal";
    exit;
}

// Si la respuesta es una lista M3U8, reescribimos las rutas internas
if (strpos($data, '#EXTM3U') !== false) {
    header("Content-Type: application/vnd.apple.mpegurl");
    
    $lines = explode("\n", $data);
    $newLines = [];

    foreach ($lines as $line) {
        $lineTrim = trim($line);
        // Si no es un comentario/etiqueta y no está vacía
        if (!empty($lineTrim) && $lineTrim[0] !== '#') {
            // Convertir ruta relativa a absoluta si es necesario
            if (strpos($lineTrim, 'http://') !== 0 && strpos($lineTrim, 'https://') !== 0) {
                if ($lineTrim[0] === '/') {
                    $absUrl = $urlParts['scheme'] . '://' . $urlParts['host'] . $lineTrim;
                } else {
                    $absUrl = $baseUrl . $lineTrim;
                }
            } else {
                $absUrl = $lineTrim;
            }
            // Hacer que la sub-lista o fragmento se descargue también vía proxy.php
            $newLines[] = 'proxy.php?url=' . urlencode($absUrl);
        } else {
            $newLines[] = $line;
        }
    }
    echo implode("\n", $newLines);
} else {
    // Si es un fragmento de video (.ts), se entrega directamente
    header("Content-Type: video/MP2T");
    echo $data;
}
?>