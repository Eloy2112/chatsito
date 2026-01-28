<?php
require_once '../config/database.php';

class Report {
    private $conn;
    private $table_name = "reports";

    public $id;
    public $title;
    public $description;
    public $report_type;
    public $generated_by;
    public $filters;
    public $report_data;
    public $report_file_path;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new report entry
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, description=:description, report_type=:report_type, 
                      generated_by=:generated_by, filters=:filters, 
                      report_data=:report_data, report_file_path=:report_file_path";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":report_type", $this->report_type);
        $stmt->bindParam(":generated_by", $this->generated_by);
        $stmt->bindParam(":filters", $this->filters);
        $stmt->bindParam(":report_data", $this->report_data);
        $stmt->bindParam(":report_file_path", $this->report_file_path);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get report by ID
    public function get_by_id($id) {
        $query = "SELECT r.*, u.username, u.first_name, u.last_name 
                  FROM " . $this->table_name . " r
                  JOIN users u ON r.generated_by = u.id
                  WHERE r.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->report_type = $row['report_type'];
            $this->generated_by = $row['generated_by'];
            $this->filters = $row['filters'];
            $this->report_data = $row['report_data'];
            $this->report_file_path = $row['report_file_path'];
            return true;
        }
        return false;
    }

    // Get all reports for a user
    public function get_all_by_user($user_id) {
        $query = "SELECT r.*, u.username, u.first_name, u.last_name 
                  FROM " . $this->table_name . " r
                  JOIN users u ON r.generated_by = u.id
                  WHERE r.generated_by = ?
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    // Get all reports (for admins and supervisors)
    public function get_all_reports($limit = 100) {
        $query = "SELECT r.*, u.username, u.first_name, u.last_name 
                  FROM " . $this->table_name . " r
                  JOIN users u ON r.generated_by = u.id
                  ORDER BY r.created_at DESC 
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Update report
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, description=:description, report_type=:report_type, 
                      filters=:filters, report_data=:report_data, report_file_path=:report_file_path 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':report_type', $this->report_type);
        $stmt->bindParam(':filters', $this->filters);
        $stmt->bindParam(':report_data', $this->report_data);
        $stmt->bindParam(':report_file_path', $this->report_file_path);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete report
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