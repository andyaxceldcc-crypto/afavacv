<?php
/**
 * Modelo de Categoría
 */

namespace App\Models;

use App\Config\Database;

class Category {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las categorías
     */
    public function getAll() {
        $stmt = $this->db->prepare(
            "SELECT * FROM categorias WHERE activa = 1 ORDER BY orden, nombre"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener categoría por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM categorias WHERE id = ? AND activa = 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crear categoría (admin)
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO categorias (nombre, descripcion, icono, color, orden)
             VALUES (?, ?, ?, ?, ?)"
        );
        
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['icono'] ?? null,
            $data['color'] ?? '#FF6B6B',
            $data['orden'] ?? 0
        ]);
    }

    /**
     * Actualizar categoría (admin)
     */
    public function update($id, $data) {
        $allowed = ['nombre', 'descripcion', 'icono', 'color', 'orden'];
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
        $sql = "UPDATE categorias SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Desactivar categoría (admin)
     */
    public function deactivate($id) {
        $stmt = $this->db->prepare("UPDATE categorias SET activa = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Obtener cantidad de videos por categoría
     */
    public function getVideoCount($category_id) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM videos WHERE categoria_id = ? AND estado = 'publicado'"
        );
        $stmt->execute([$category_id]);
        return $stmt->fetch()['total'];
    }
}
