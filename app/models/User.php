<?php
/**
 * Modelo de Usuario
 */

namespace App\Models;

use App\Config\Database;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener usuario por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT id, nombre, email, foto_perfil, bio, rol, estado, fecha_registro, ultimo_acceso
             FROM usuarios WHERE id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Obtener usuario por email
     */
    public function getByEmail($email) {
        $stmt = $this->db->prepare(
            "SELECT id, nombre, email, contraseña, rol, estado FROM usuarios WHERE email = ?"
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Actualizar perfil de usuario
     */
    public function updateProfile($id, $data) {
        $allowed_fields = ['nombre', 'bio'];
        $updates = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $updates[] = "$key = ?";
                $values[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE usuarios SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Actualizar foto de perfil
     */
    public function updateProfilePhoto($id, $photo_path) {
        $stmt = $this->db->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
        return $stmt->execute([$photo_path, $id]);
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword($id, $new_password) {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
        return $stmt->execute([$hashed, $id]);
    }

    /**
     * Obtener número de suscriptores
     */
    public function getSubscribersCount($user_id) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM suscripciones WHERE canal_usuario_id = ?"
        );
        $stmt->execute([$user_id]);
        return $stmt->fetch()['total'];
    }

    /**
     * Obtener número de videos
     */
    public function getVideosCount($user_id) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM videos WHERE usuario_id = ? AND estado = 'publicado'"
        );
        $stmt->execute([$user_id]);
        return $stmt->fetch()['total'];
    }

    /**
     * Obtener si está suscrito a un canal
     */
    public function isSubscribed($user_id, $channel_id) {
        $stmt = $this->db->prepare(
            "SELECT id FROM suscripciones WHERE usuario_id = ? AND canal_usuario_id = ?"
        );
        $stmt->execute([$user_id, $channel_id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Suscribirse a un canal
     */
    public function subscribe($user_id, $channel_id) {
        if ($user_id === $channel_id) {
            return ['success' => false, 'message' => 'No puedes suscribirte a ti mismo'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO suscripciones (usuario_id, canal_usuario_id) VALUES (?, ?)"
        );
        
        try {
            $stmt->execute([$user_id, $channel_id]);
            return ['success' => true, 'message' => 'Suscrito al canal'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Ya estás suscrito a este canal'];
        }
    }

    /**
     * Desuscribirse de un canal
     */
    public function unsubscribe($user_id, $channel_id) {
        $stmt = $this->db->prepare(
            "DELETE FROM suscripciones WHERE usuario_id = ? AND canal_usuario_id = ?"
        );
        return $stmt->execute([$user_id, $channel_id]);
    }

    /**
     * Obtener canales suscritos
     */
    public function getSubscribedChannels($user_id, $limit = 10, $offset = 0) {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.nombre, u.foto_perfil, u.bio
             FROM usuarios u
             INNER JOIN suscripciones s ON u.id = s.canal_usuario_id
             WHERE s.usuario_id = ?
             ORDER BY s.fecha_suscripcion DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$user_id, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Suspender usuario (admin)
     */
    public function suspend($id, $reason = null) {
        $stmt = $this->db->prepare(
            "UPDATE usuarios SET estado = 'suspendido' WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Activar usuario (admin)
     */
    public function activate($id) {
        $stmt = $this->db->prepare(
            "UPDATE usuarios SET estado = 'activo' WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Listar todos los usuarios (admin)
     */
    public function getAll($limit = 20, $offset = 0) {
        $stmt = $this->db->prepare(
            "SELECT id, nombre, email, rol, estado, fecha_registro
             FROM usuarios
             ORDER BY fecha_registro DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
}
