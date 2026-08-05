<?php
session_start();
header("Content-Type: application/json");

// CONEXIÓN DIRECTA A MYSQL
$host = 'localhost';
$db   = 'andyaxce_cine';     // Cambia por el nombre de tu BD
$user = 'andyaxce_admin';    // Cambia por tu usuario MySQL
$pass = 'TuContraseñaAqui';  // Cambia por tu clave MySQL

try {
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
     ]);
} catch (\PDOException $e) {
     echo json_encode(['success' => false, 'message' => 'Error de conexión']);
     exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';
$stripe_secret_key = "sk_test_TU_CLAVE_STRIPE"; 

// 1. REGISTRO
if ($action === 'register') {
    $email        = trim($data['email'] ?? '');
    $phone        = trim($data['phone'] ?? '');
    $password     = $data['password'] ?? '';
    $confirm_pass = $data['confirm_password'] ?? '';
    $method       = $data['method'] ?? 'stripe'; 
    $lang         = $data['lang'] ?? 'es'; 
    $payment_ref  = trim($data['payment_ref'] ?? ''); 

    if (empty($email) || empty($phone) || empty($password) || empty($confirm_pass)) {
        echo json_encode(['success' => false, 'message' => 'Completa todos los campos']);
        exit;
    }

    if ($password !== $confirm_pass) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El correo ya está registrado']);
        exit;
    }

    $active = ($method === 'yape') ? 1 : 0;
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (email, phone, password, method, lang, payment_ref, active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$email, $phone, $hashedPassword, $method, $lang, $payment_ref, $active]);

    if ($method === 'stripe') {
        $currency = ($lang === 'es') ? 'pen' : 'usd';
        $amount   = ($lang === 'es') ? 1500 : 500;
        $redirect = ($lang === 'es') ? 'login-es.html' : 'login-en.html';

        $stripePayload = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => 'Acceso VIP Películas'],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $email,
            'client_reference_id' => $email,
            'success_url' => "https://andyaxceldcc.com/{$redirect}?status=success",
            'cancel_url' => "https://andyaxceldcc.com/{$redirect}?status=cancel",
        ];

        $ch = curl_init("https://api.stripe.com/v1/checkout/sessions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $stripe_secret_key . ":");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($stripePayload));
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        echo json_encode(['success' => true, 'checkout_url' => $res['url'] ?? '']);
        exit;
    }

    $_SESSION['user'] = $email;
    $_SESSION['lang'] = $lang;
    echo json_encode(['success' => true, 'redirect' => 'index-es.html']);
    exit;
}

// 2. LOGIN
if ($action === 'login') {
    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if (!$user['active']) {
            echo json_encode(['success' => false, 'message' => 'Pago pendiente de verificación']);
            exit;
        }
        $_SESSION['user'] = $user['email'];
        $_SESSION['lang'] = $user['lang'];

        $target = ($user['lang'] === 'es') ? 'index-es.html' : 'index-en.html';
        echo json_encode(['success' => true, 'redirect' => $target]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
    exit;
}

// 3. CHECK SESIÓN
if ($action === 'check') {
    echo json_encode(['logged' => isset($_SESSION['user']), 'lang' => $_SESSION['lang'] ?? 'es']);
    exit;
}
?>