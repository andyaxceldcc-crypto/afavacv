<?php
/**
 * Clase de Conexión a Base de Datos
 * Maneja todas las conexiones PDO con la base de datos
 */

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    private $statement;
    private $error;

    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            
            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (DEBUG_MODE) {
                echo "Error de Conexión: " . $this->error;
            }
            die('Error de conexión a la base de datos');
        }
    }

    /**
     * Obtener instancia única de la conexión
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Preparar consulta
     */
    public function prepare($sql) {
        $this->statement = $this->connection->prepare($sql);
        return $this;
    }

    /**
     * Vincular valores a la consulta preparada
     */
    public function bind($param, $value, $type = PDO::PARAM_STR) {
        if (is_int($value)) {
            $type = PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            $type = PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            $type = PDO::PARAM_NULL;
        }
        
        $this->statement->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Ejecutar consulta
     */
    public function execute() {
        try {
            return $this->statement->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            if (DEBUG_MODE) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Obtener un único resultado
     */
    public function single() {
        $this->execute();
        return $this->statement->fetch();
    }

    /**
     * Obtener todos los resultados
     */
    public function resultSet() {
        $this->execute();
        return $this->statement->fetchAll();
    }

    /**
     * Obtener cantidad de filas afectadas
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }

    /**
     * Obtener ID de última inserción
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Obtener error
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Comenzar transacción
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollBack() {
        return $this->connection->rollBack();
    }

    /**
     * Evitar instancias duplicadas
     */
    private function __clone() {}
    private function __wakeup() {}
}
