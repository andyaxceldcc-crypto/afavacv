<?php
session_start();
header("Content-Type: application/json");

// CONEXIÓN A LA BASE DE DATOS EN ESPAÑOL
$servidor   = 'localhost';
$base_datos = 'andyaxce_cine';     // PON AQUÍ EL NOMBRE DE TU BASE DE DATOS
$usuario    = 'andyaxce_admin';    // PON AQUÍ TU USUARIO
$clave      = 'TuContraseñaAqui';  // PON AQUÍ TU CONTRASEÑA

try {
     $pdo = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8mb4", $usuario, $clave, [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
     ]);
} catch (\PDOException $e) {
     echo json_encode(['success' => false, 'message' => 'Error de conexión']);
     exit;
}

$datos = json_decode(file_get_contents("php://input"), true);
$accion = $_GET['action'] ?? '';
$llave_stripe = "sk_test_TU_CLAVE_STRIPE"; 

// 1. REGISTRO
if ($accion === 'register') {
    $correo           = trim($datos['email'] ?? '');
    $telefono         = trim($datos['phone'] ?? '');
    $contrasena       = $datos['password'] ?? '';
    $confirmar_clave  = $datos['confirm_password'] ?? '';
    $metodo_pago      = $datos['method'] ?? 'stripe'; 
    $idioma           = $datos['lang'] ?? 'es'; 
    $referencia_pago  = trim($datos['payment_ref'] ?? ''); 

    if (empty($correo) || empty($telefono) || empty($contrasena) || empty($confirmar_clave)) {
        echo json_encode(['success' => false, 'message' => 'Completa todos los campos']);
        exit;
    }

    if ($contrasena !== $confirmar_clave) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        exit;
    }

    $consulta = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $consulta->execute([$correo]);
    if ($consulta->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El correo ya está registrado']);
        exit;
    }

    $activo = ($metodo_pago === 'yape') ? 1 : 0;
    $clave_encriptada = password_hash($contrasena, PASSWORD_BCRYPT);

    $consulta = $pdo->prepare("INSERT INTO users (email, phone, password, method, lang, payment_ref, active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $consulta->execute([$correo, $telefono, $clave_encriptada, $metodo_pago, $idioma, $referencia_pago, $activo]);

    if ($metodo_pago === 'stripe') {
        $moneda = ($idioma === 'es') ? 'pen' : 'usd';
        $monto  = ($idioma === 'es') ? 1500 : 500;
        $destino = ($idioma === 'es') ? 'login-es.html' : 'login-en.html';

        $datos_stripe = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $moneda,
                    'product_data' => ['name' => 'Acceso VIP Películas'],
                    'unit_amount' => $monto,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $correo,
            'client_reference_id' => $correo,
            'success_url' => "https://andyaxceldcc.com/{$destino}?status=success",
            'cancel_url' => "https://andyaxceldcc.com/{$destino}?status=cancel",
        ];

        $ch = curl_init("https://api.stripe.com/v1/checkout/sessions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $llave_stripe . ":");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datos_stripe));
        $respuesta = json_decode(curl_exec($ch), true);
        curl_close($ch);

        echo json_encode(['success' => true, 'checkout_url' => $respuesta['url'] ?? '']);
        exit;
    }

    $_SESSION['user'] = $correo;
    $_SESSION['lang'] = $idioma;
    echo json_encode(['success' => true, 'redirect' => 'index-es.html']);
    exit;
}

// 2. INICIAR SESIÓN
if ($accion === 'login') {
    $correo     = trim($datos['email'] ?? '');
    $contrasena = $datos['password'] ?? '';

    $consulta = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $consulta->execute([$correo]);
    $usuario_encontrado = $consulta->fetch();

    if ($usuario_encontrado && password_verify($contrasena, $usuario_encontrado['password'])) {
        if (!$usuario_encontrado['active']) {
            echo json_encode(['success' => false, 'message' => 'Pago pendiente de verificación']);
            exit;
        }
        $_SESSION['user'] = $usuario_encontrado['email'];
        $_SESSION['lang'] = $usuario_encontrado['lang'];

        $pagina_destino = ($usuario_encontrado['lang'] === 'es') ? 'index-es.html' : 'index-en.html';
        echo json_encode(['success' => true, 'redirect' => $pagina_destino]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
    exit;
}

// 3. VERIFICAR SESIÓN
if ($accion === 'check') {
    echo json_encode(['logged' => isset($_SESSION['user']), 'lang' => $_SESSION['lang'] ?? 'es']);
    exit;
}
?>