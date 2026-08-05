<?php
// api/auth.php
session_start();
header("Content-Type: application/json");

// Incluir la conexión a la base de datos
require_once __DIR__ . '/../config/base.php';

$datos = json_decode(file_get_contents("php://input"), true);
$accion = $_GET['action'] ?? '';

// Tu clave secreta de prueba o producción de Stripe
$llave_stripe = "sk_test_TU_CLAVE_STRIPE"; 

// ==========================================
// 1. REGISTRO DE USUARIO (YAPE O STRIPE)
// ==========================================
if ($accion === 'register') {
    $correo           = trim($datos['email'] ?? '');
    $telefono         = trim($datos['phone'] ?? '');
    $contrasena       = $datos['password'] ?? '';
    $confirmar_clave  = $datos['confirm_password'] ?? '';
    $metodo_pago      = $datos['method'] ?? 'yape'; 
    $idioma           = $datos['lang'] ?? 'es'; 
    $referencia_pago  = trim($datos['payment_ref'] ?? ''); // Número de operación Yape

    if (empty($correo) || empty($telefono) || empty($contrasena) || empty($confirmar_clave)) {
        echo json_encode(['success' => false, 'message' => 'Completa todos los campos obligatorios']);
        exit;
    }

    if ($contrasena !== $confirmar_clave) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        exit;
    }

    // Verificar si el correo ya existe
    $consulta = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $consulta->execute([$correo]);
    if ($consulta->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El correo ya se encuentra registrado']);
        exit;
    }

    // Si es Yape, active=0 hasta que verifiques la operación. Si es Stripe, se activará tras el pago.
    $activo = ($metodo_pago === 'yape') ? 0 : 0; 
    $clave_encriptada = password_hash($contrasena, PASSWORD_BCRYPT);

    // Guardar en MySQL
    $consulta = $pdo->prepare("INSERT INTO users (email, phone, password, method, lang, payment_ref, active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $consulta->execute([$correo, $telefono, $clave_encriptada, $metodo_pago, $idioma, $referencia_pago, $activo]);

    // Si eligió pago por STRIPE
    if ($metodo_pago === 'stripe') {
        $moneda  = 'pen'; // Cambia a 'usd' si prefieres dólares
        $monto   = 1500;  // Monto en céntimos (S/ 15.00)

        $datos_stripe = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $moneda,
                    'product_data' => ['name' => 'Acceso VIP Mensual - Ojo IP'],
                    'unit_amount' => $monto,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $correo,
            'client_reference_id' => $correo,
            'success_url' => "http://localhost:81/ojo%20ip/login-es.html?status=success",
            'cancel_url' => "http://localhost:81/ojo%20ip/login-es.html?status=cancel",
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

    // Respuesta exitosa para Yape (Queda pendiente de revisión o activa si decides ponerlo en 1)
    echo json_encode(['success' => true, 'message' => 'Registro guardado con éxito']);
    exit;
}

// ==========================================
// 2. INICIO DE SESIÓN (LOGIN)
// ==========================================
if ($accion === 'login') {
    $correo     = trim($datos['email'] ?? '');
    $contrasena = $datos['password'] ?? '';

    $consulta = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $consulta->execute([$correo]);
    $usuario = $consulta->fetch();

    if ($usuario && password_verify($contrasena, $usuario['password'])) {
        
        // Validar si su pago/cuenta está activo en SQL
        if (!$usuario['active']) {
            echo json_encode(['success' => false, 'message' => 'Tu cuenta está pendiente de verificación de pago (Yape) o desactivada.']);
            exit;
        }

        // Crear sesión de usuario
        $_SESSION['user'] = $usuario['email'];
        $_SESSION['lang'] = $usuario['lang'];

        echo json_encode(['success' => true, 'redirect' => 'index-es.html']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos']);
    exit;
}

// ==========================================
// 3. COMPROBAR SESIÓN ACTIVA (Para el index)
// ==========================================
if ($accion === 'check') {
    echo json_encode([
        'logged' => isset($_SESSION['user']),
        'lang' => $_SESSION['lang'] ?? 'es'
    ]);
    exit;
}
?>