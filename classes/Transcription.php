<?php
require_once '../config/database.php';

class Transcription {
    private $conn;
    private $table_name = "transcriptions";

    public $id;
    public $audio_recording_id;
    public $transcription_text;
    public $confidence_score;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new transcription entry
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET audio_recording_id=:audio_recording_id, 
                      transcription_text=:transcription_text, 
                      confidence_score=:confidence_score";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":audio_recording_id", $this->audio_recording_id);
        $stmt->bindParam(":transcription_text", $this->transcription_text);
        $stmt->bindParam(":confidence_score", $this->confidence_score);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get transcription by audio recording ID
    public function get_by_audio_id($audio_recording_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE audio_recording_id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $audio_recording_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->audio_recording_id = $row['audio_recording_id'];
            $this->transcription_text = $row['transcription_text'];
            $this->confidence_score = $row['confidence_score'];
            return true;
        }
        return false;
    }

    // Update transcription
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET transcription_text=:transcription_text, 
                      confidence_score=:confidence_score 
                  WHERE audio_recording_id=:audio_recording_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':transcription_text', $this->transcription_text);
        $stmt->bindParam(':confidence_score', $this->confidence_score);
        $stmt->bindParam(':audio_recording_id', $this->audio_recording_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete transcription
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE audio_recording_id = :audio_recording_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':audio_recording_id', $this->audio_recording_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}