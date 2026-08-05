<?php
/**
 * Clase de Autenticación - JWT y Sesión
 * Maneja login, logout y validación de tokens
 */

namespace App\Includes;

use App\Config\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private $db;
    private $user = null;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registrar nuevo usuario
     */
    public function register($nombre, $email, $password, $password_confirm) {
        // Validaciones
        if (empty($nombre) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Todos los campos son requeridos'];
        }

        if ($password !== $password_confirm) {
            return ['success' => false, 'message' => 'Las contraseñas no coinciden'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'El email no es válido'];
        }

        // Verificar si el email ya existe
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }

        // Encriptar contraseña
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insertar nuevo usuario
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO usuarios (nombre, email, contraseña, rol) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$nombre, $email, $hashed_password, 'usuario']);

            return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al registrar usuario'];
        }
    }

    /**
     * Iniciar sesión
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email y contraseña requeridos'];
        }

        // Obtener usuario
        $stmt = $this->db->prepare(
            "SELECT id, nombre, email, contraseña, rol, estado FROM usuarios WHERE email = ?"
        );
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        }

        $user = $stmt->fetch();

        // Verificar estado del usuario
        if ($user['estado'] !== 'activo') {
            return ['success' => false, 'message' => 'Esta cuenta ha sido suspendida'];
        }

        // Verificar contraseña
        if (!password_verify($password, $user['contraseña'])) {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        }

        // Generar JWT
        $payload = [
            'iat' => time(),
            'exp' => time() + JWT_EXPIRATION,
            'id' => $user['id'],
            'email' => $user['email'],
            'rol' => $user['rol']
        ];

        $jwt = JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);

        // Actualizar último acceso
        $stmt = $this->db->prepare(
            "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?"
        );
        $stmt->execute([$user['id']]);

        // Guardar en sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_rol'] = $user['rol'];
        $_SESSION['jwt'] = $jwt;

        return [
            'success' => true,
            'message' => 'Sesión iniciada correctamente',
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'email' => $user['email'],
                'rol' => $user['rol']
            ],
            'token' => $jwt
        ];
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada'];
    }

    /**
     * Obtener usuario actual
     */
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        if ($this->user !== null) {
            return $this->user;
        }

        $stmt = $this->db->prepare(
            "SELECT id, nombre, email, foto_perfil, rol, estado FROM usuarios WHERE id = ?"
        );
        $stmt->execute([$_SESSION['user_id']]);

        $this->user = $stmt->fetch();
        return $this->user;
    }

    /**
     * Verificar si está autenticado
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Verificar si es administrador
     */
    public function isAdmin() {
        return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
    }

    /**
     * Validar JWT
     */
    public function validateJWT($token) {
        try {
            $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));
            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Recuperar contraseña
     */
    public function requestPasswordReset($email) {
        $stmt = $this->db->prepare("SELECT id, nombre FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Email no encontrado'];
        }

        $user = $stmt->fetch();
        $reset_token = bin2hex(random_bytes(32));
        $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token de recuperación
        $stmt = $this->db->prepare(
            "UPDATE usuarios SET reset_token = ?, reset_expiry = ? WHERE id = ?"
        );
        $stmt->execute([$reset_token, $reset_expiry, $user['id']]);

        // TODO: Enviar email con link de recuperación
        $reset_link = BASE_URL . "/recuperar-contrasena?token=" . $reset_token;

        return [
            'success' => true,
            'message' => 'Revisa tu email para recuperar tu contraseña'
        ];
    }

    /**
     * Restablecer contraseña
     */
    public function resetPassword($token, $new_password, $confirm_password) {
        if ($new_password !== $confirm_password) {
            return ['success' => false, 'message' => 'Las contraseñas no coinciden'];
        }

        if (strlen($new_password) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres'];
        }

        // Verificar token
        $stmt = $this->db->prepare(
            "SELECT id FROM usuarios WHERE reset_token = ? AND reset_expiry > NOW()"
        );
        $stmt->execute([$token]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Token inválido o expirado'];
        }

        $user = $stmt->fetch();
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare(
            "UPDATE usuarios SET contraseña = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?"
        );
        $stmt->execute([$hashed_password, $user['id']]);

        return ['success' => true, 'message' => 'Contraseña restablecida correctamente'];
    }
}
