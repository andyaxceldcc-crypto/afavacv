<?php
/**
 * Modelo de Pago
 */

namespace App\Models;

use App\Config\Database;

class Payment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear transacción de pago
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO pagos (usuario_id, suscripcion_premium_id, monto, moneda, metodo_pago, proveedor, id_transaccion, estado, descripcion)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        return $stmt->execute([
            $data['usuario_id'],
            $data['suscripcion_premium_id'],
            $data['monto'],
            $data['moneda'] ?? 'USD',
            $data['metodo_pago'],
            $data['proveedor'],
            $data['id_transaccion'],
            'pendiente',
            $data['descripcion'] ?? null
        ]);
    }

    /**
     * Actualizar estado de pago
     */
    public function updateStatus($id, $status) {
        $valid_statuses = ['pendiente', 'completado', 'fallido', 'cancelado'];
        
        if (!in_array($status, $valid_statuses)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE pagos SET estado = ? WHERE id = ?"
        );
        return $stmt->execute([$status, $id]);
    }

    /**
     * Obtener pago por transacción
     */
    public function getByTransaction($transaction_id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM pagos WHERE id_transaccion = ?"
        );
        $stmt->execute([$transaction_id]);
        return $stmt->fetch();
    }

    /**
     * Obtener pagos de usuario
     */
    public function getByUser($user_id) {
        $stmt = $this->db->prepare(
            "SELECT p.*, sp.plan_id FROM pagos p
             JOIN suscripciones_premium sp ON p.suscripcion_premium_id = sp.id
             WHERE p.usuario_id = ?
             ORDER BY p.fecha_pago DESC"
        );
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener todos los pagos (admin)
     */
    public function getAll($limit = 20, $offset = 0) {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.nombre, u.email FROM pagos p
             JOIN usuarios u ON p.usuario_id = u.id
             ORDER BY p.fecha_pago DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
}
