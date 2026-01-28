<?php
require_once '../config/database.php';

class AudioRecording {
    private $conn;
    private $table_name = "audio_recordings";

    public $id;
    public $user_id;
    public $filename;
    public $original_filename;
    public $file_path;
    public $duration_seconds;
    public $file_size_bytes;
    public $transcription_status;
    public $analysis_status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new audio recording entry
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, filename=:filename, original_filename=:original_filename, 
                      file_path=:file_path, duration_seconds=:duration_seconds, 
                      file_size_bytes=:file_size_bytes, transcription_status=:transcription_status, 
                      analysis_status=:analysis_status";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":filename", $this->filename);
        $stmt->bindParam(":original_filename", $this->original_filename);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":duration_seconds", $this->duration_seconds);
        $stmt->bindParam(":file_size_bytes", $this->file_size_bytes);
        $stmt->bindParam(":transcription_status", $this->transcription_status);
        $stmt->bindParam(":analysis_status", $this->analysis_status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Get audio recording by ID
    public function get_by_id($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->filename = $row['filename'];
            $this->original_filename = $row['original_filename'];
            $this->file_path = $row['file_path'];
            $this->duration_seconds = $row['duration_seconds'];
            $this->file_size_bytes = $row['file_size_bytes'];
            $this->transcription_status = $row['transcription_status'];
            $this->analysis_status = $row['analysis_status'];
            return true;
        }
        return false;
    }

    // Get all recordings for a specific user
    public function get_all_by_user($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = ? ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    // Get all recordings (for admins and supervisors)
    public function get_all_recordings($limit = 100) {
        $query = "SELECT ar.*, u.username, u.first_name, u.last_name 
                  FROM " . $this->table_name . " ar 
                  JOIN users u ON ar.user_id = u.id 
                  ORDER BY ar.created_at DESC 
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Update transcription status
    public function update_transcription_status($status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET transcription_status = :status 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update analysis status
    public function update_analysis_status($status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET analysis_status = :status 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete audio recording
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