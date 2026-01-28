<?php
require_once '../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $first_name;
    public $last_name;
    public $department;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, email=:email, password_hash=:password_hash, 
                      role=:role, first_name=:first_name, last_name=:last_name, 
                      department=:department";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":department", $this->department);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if user exists by username
    public function userExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    // Get user by username
    public function get_user_by_username() {
        $query = "SELECT id, username, email, password_hash, role, first_name, last_name, department, status 
                  FROM " . $this->table_name . " 
                  WHERE username = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password = $row['password_hash'];
            $this->role = $row['role'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->department = $row['department'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // Get user by ID
    public function get_user_by_id($id) {
        $query = "SELECT id, username, email, role, first_name, last_name, department, status 
                  FROM " . $this->table_name . " 
                  WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->department = $row['department'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // Get all users by role
    public function get_users_by_role($role) {
        $query = "SELECT id, username, email, role, first_name, last_name, department, status, created_at 
                  FROM " . $this->table_name . " 
                  WHERE role = ?
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $role);
        $stmt->execute();

        return $stmt;
    }

    // Update user profile
    public function update_profile() {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name=:first_name, last_name=:last_name, department=:department 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Change user password
    public function change_password($new_password_hash) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password_hash=:password_hash 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':password_hash', $new_password_hash);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete user
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}