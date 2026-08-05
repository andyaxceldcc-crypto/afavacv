<?php
/**
 * Clase de Seguridad - Protección contra vulnerabilidades
 * CSRF, XSS, SQL Injection, etc.
 */

namespace App\Includes;

class Security {
    /**
     * Generar token CSRF
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validar token CSRF
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Escapar salida HTML (prevenir XSS)
     */
    public static function escape($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar entrada
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return trim(strip_tags($data));
    }

    /**
     * Validar email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar URL
     */
    public static function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Generar hash seguro
     */
    public static function generateHash($data) {
        return hash('sha256', $data . time());
    }

    /**
     * Generar contraseña segura
     */
    public static function generatePassword($length = 12) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $password;
    }

    /**
     * Validar fortaleza de contraseña
     */
    public static function validatePasswordStrength($password) {
        $strength = 0;
        $feedback = [];

        if (strlen($password) >= 8) $strength++; else $feedback[] = 'Mínimo 8 caracteres';
        if (preg_match('/[a-z]/', $password)) $strength++; else $feedback[] = 'Incluir letras minúsculas';
        if (preg_match('/[A-Z]/', $password)) $strength++; else $feedback[] = 'Incluir letras mayúsculas';
        if (preg_match('/[0-9]/', $password)) $strength++; else $feedback[] = 'Incluir números';
        if (preg_match('/[!@#$%^&*]/', $password)) $strength++; else $feedback[] = 'Incluir caracteres especiales';

        return [
            'strength' => $strength,
            'feedback' => $feedback,
            'valid' => $strength >= 3
        ];
    }

    /**
     * Obtener IP del cliente
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Rate limiting básico
     */
    public static function checkRateLimit($identifier, $limit = 5, $window = 60) {
        $key = 'rate_limit_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $window];
        }

        if (time() > $_SESSION[$key]['reset_time']) {
            $_SESSION[$key] = ['count' => 0, 'reset_time' => time() + $window];
        }

        if ($_SESSION[$key]['count'] >= $limit) {
            return false;
        }

        $_SESSION[$key]['count']++;
        return true;
    }

    /**
     * Registrar actividad
     */
    public static function logActivity($user_id, $action, $description = null, $ip = null) {
        $db = \App\Config\Database::getInstance()->getConnection();
        $ip = $ip ?? self::getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = $db->prepare(
            "INSERT INTO registros_actividad (usuario_id, accion, descripcion, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$user_id, $action, $description, $ip, $user_agent]);
    }
}
