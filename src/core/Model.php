<?php
// src/core/Model.php
require_once __DIR__ . '/db.php';

class Model {
    protected $conn;

    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }

    protected function lastError($stmt = null) {
        if ($stmt instanceof PDOStatement) {
            return $stmt->errorInfo();
        }
        return null;
    }
}

?>