<?php
/**
 * Modelo de Video
 */

namespace App\Models;

use App\Config\Database;

class Video {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener video por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.nombre as autor_nombre, u.foto_perfil, c.nombre as categoria_nombre
             FROM videos v
             JOIN usuarios u ON v.usuario_id = u.id
             JOIN categorias c ON v.categoria_id = c.id
             WHERE v.id = ? AND v.estado = 'publicado'"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crear nuevo video
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO videos (usuario_id, titulo, descripcion, categoria_id, archivo_video, miniatura, duracion, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $result = $stmt->execute([
            $data['usuario_id'],
            $data['titulo'],
            $data['descripcion'] ?? null,
            $data['categoria_id'],
            $data['archivo_video'],
            $data['miniatura'] ?? null,
            $data['duracion'] ?? 0,
            'borrador'
        ]);

        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Actualizar video
     */
    public function update($id, $data) {
        $allowed = ['titulo', 'descripcion', 'categoria_id', 'premium', 'miniatura'];
        $updates = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $updates[] = "$key = ?";
                $values[] = $value;
            }
        }

        if (empty($updates)) return false;

        $values[] = $id;
        $sql = "UPDATE videos SET " . implode(', ', $updates) . ", fecha_actualizacion = NOW() WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Publicar video
     */
    public function publish($id) {
        $stmt = $this->db->prepare(
            "UPDATE videos SET estado = 'publicado', fecha_publicacion = NOW() WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Eliminar video
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE videos SET estado = 'eliminado' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Incrementar vistas
     */
    public function incrementViews($id) {
        $stmt = $this->db->prepare("UPDATE videos SET vistas = vistas + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Obtener videos por usuario
     */
    public function getByUser($user_id, $limit = 12, $offset = 0) {
        $stmt = $this->db->prepare(
            "SELECT id, titulo, miniatura, duracion, vistas, fecha_publicacion
             FROM videos
             WHERE usuario_id = ? AND estado = 'publicado'
             ORDER BY fecha_publicacion DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$user_id, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener videos por categoría
     */
    public function getByCategory($category_id, $limit = 12, $offset = 0) {
        $stmt = $this->db->prepare(
            "SELECT v.id, v.titulo, v.miniatura, v.duracion, v.vistas, v.fecha_publicacion, u.nombre as autor
             FROM videos v
             JOIN usuarios u ON v.usuario_id = u.id
             WHERE v.categoria_id = ? AND v.estado = 'publicado'
             ORDER BY v.fecha_publicacion DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$category_id, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar videos
     */
    public function search($query, $limit = 12, $offset = 0) {
        $search_term = '%' . $query . '%';
        $stmt = $this->db->prepare(
            "SELECT v.id, v.titulo, v.miniatura, v.duracion, v.vistas, u.nombre as autor
             FROM videos v
             JOIN usuarios u ON v.usuario_id = u.id
             WHERE (v.titulo LIKE ? OR v.descripcion LIKE ?) AND v.estado = 'publicado'
             ORDER BY v.vistas DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$search_term, $search_term, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener videos tendencia
     */
    public function getTrending($limit = 12) {
        $stmt = $this->db->prepare(
            "SELECT v.id, v.titulo, v.miniatura, v.duracion, v.vistas, u.nombre as autor
             FROM videos v
             JOIN usuarios u ON v.usuario_id = u.id
             WHERE v.estado = 'publicado' AND v.fecha_publicacion > DATE_SUB(NOW(), INTERVAL 7 DAY)
             ORDER BY v.vistas DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener videos recientes
     */
    public function getRecent($limit = 12) {
        $stmt = $this->db->prepare(
            "SELECT v.id, v.titulo, v.miniatura, v.duracion, v.vistas, v.fecha_publicacion, u.nombre as autor
             FROM videos v
             JOIN usuarios u ON v.usuario_id = u.id
             WHERE v.estado = 'publicado'
             ORDER BY v.fecha_publicacion DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Agregar a favoritos
     */
    public function addFavorite($user_id, $video_id) {
        $stmt = $this->db->prepare(
            "INSERT INTO favoritos (usuario_id, video_id) VALUES (?, ?)"
        );
        return $stmt->execute([$user_id, $video_id]);
    }

    /**
     * Remover de favoritos
     */
    public function removeFavorite($user_id, $video_id) {
        $stmt = $this->db->prepare(
            "DELETE FROM favoritos WHERE usuario_id = ? AND video_id = ?"
        );
        return $stmt->execute([$user_id, $video_id]);
    }

    /**
     * Verificar si está en favoritos
     */
    public function isFavorited($user_id, $video_id) {
        $stmt = $this->db->prepare(
            "SELECT id FROM favoritos WHERE usuario_id = ? AND video_id = ?"
        );
        $stmt->execute([$user_id, $video_id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Obtener favoritos del usuario
     */
    public function getFavorites($user_id, $limit = 12, $offset = 0) {
        $stmt = $this->db->prepare(
            "SELECT v.id, v.titulo, v.miniatura, v.duracion, v.vistas, u.nombre as autor
             FROM videos v
             JOIN usuarios u ON v.usuario_id = u.id
             JOIN favoritos f ON v.id = f.video_id
             WHERE f.usuario_id = ? AND v.estado = 'publicado'
             ORDER BY f.fecha_agregado DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$user_id, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Registrar reproducción
     */
    public function recordPlayback($user_id, $video_id, $time = 0) {
        $stmt = $this->db->prepare(
            "INSERT INTO reproducciones_historial (usuario_id, video_id, tiempo_reproduccion)
             VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE tiempo_reproduccion = ?"
        );
        return $stmt->execute([$user_id, $video_id, $time, $time]);
    }

    /**
     * Obtener historial de reproducción
     */
    public function getPlaybackHistory($user_id, $limit = 20) {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT v.id, v.titulo, v.miniatura, rh.fecha_reproduccion
             FROM reproducciones_historial rh
             JOIN videos v ON rh.video_id = v.id
             WHERE rh.usuario_id = ?
             ORDER BY rh.fecha_reproduccion DESC
             LIMIT ?"
        );
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    }
}
