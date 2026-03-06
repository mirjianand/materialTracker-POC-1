<?php
// src/models/User.php

require_once __DIR__ . '/../core/Model.php';

class User extends Model {
    protected $table = 'users';

    // User Properties
    public $id;
    public $emp_id;
    public $name;
    public $email;
    public $role;
    public $start_date;
    public $end_date;
    public $designation;
    public $is_active;

    public function __construct() {
        parent::__construct();
    }

    // Get all users
    public function read() {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single user
    public function read_single($id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        if($row) {
            $this->id = $row['id'];
            $this->emp_id = $row['emp_id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->designation = $row['designation'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    // Create user
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' (emp_id, name, email, role, start_date, end_date, designation, is_active)
                  VALUES (:emp_id, :name, :email, :role, :start_date, :end_date, :designation, :is_active)';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->emp_id = htmlspecialchars(strip_tags($this->emp_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->designation = htmlspecialchars(strip_tags($this->designation));
        $this->is_active = (int)$this->is_active;

        // Bind data
        $stmt->bindParam(':emp_id', $this->emp_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':designation', $this->designation);
        $stmt->bindParam(':is_active', $this->is_active, PDO::PARAM_INT);

        if($stmt->execute()) {
            return true;
        }

        $err = $this->lastError($stmt);
        error_log('User create error: ' . json_encode($err));
        return false;
    }

    // Update user
    public function update() {
        $query = 'UPDATE ' . $this->table . '
                  SET emp_id = :emp_id, name = :name, email = :email, role = :role, start_date = :start_date, end_date = :end_date, designation = :designation, is_active = :is_active
                  WHERE id = :id';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->id = (int)$this->id;
        $this->emp_id = htmlspecialchars(strip_tags($this->emp_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->designation = htmlspecialchars(strip_tags($this->designation));
        $this->is_active = (int)$this->is_active;

        // Bind data
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':emp_id', $this->emp_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':designation', $this->designation);
        $stmt->bindParam(':is_active', $this->is_active, PDO::PARAM_INT);

        if($stmt->execute()) {
            return true;
        }

        $err = $this->lastError($stmt);
        error_log('User update error: ' . json_encode($err));
        return false;
    }

    // Delete user
    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $this->id = (int)$this->id;
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        if($stmt->execute()) {
            return true;
        }

        $err = $this->lastError($stmt);
        error_log('User delete error: ' . json_encode($err));
        return false;
    }
}
?>