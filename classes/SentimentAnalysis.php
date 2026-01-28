<?php
require_once '../config/database.php';

class SentimentAnalysis {
    private $conn;
    private $table_name = "sentiment_analysis";

    public $id;
    public $audio_recording_id;
    public $sentiment_label;
    public $sentiment_score;
    public $emotions;
    public $key_phrases;
    public $topics;
    public $analysis_summary;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new sentiment analysis entry
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET audio_recording_id=:audio_recording_id, 
                      sentiment_label=:sentiment_label, 
                      sentiment_score=:sentiment_score, 
                      emotions=:emotions, 
                      key_phrases=:key_phrases, 
                      topics=:topics, 
                      analysis_summary=:analysis_summary";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":audio_recording_id", $this->audio_recording_id);
        $stmt->bindParam(":sentiment_label", $this->sentiment_label);
        $stmt->bindParam(":sentiment_score", $this->sentiment_score);
        $stmt->bindParam(":emotions", $this->emotions);
        $stmt->bindParam(":key_phrases", $this->key_phrases);
        $stmt->bindParam(":topics", $this->topics);
        $stmt->bindParam(":analysis_summary", $this->analysis_summary);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get sentiment analysis by audio recording ID
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
            $this->sentiment_label = $row['sentiment_label'];
            $this->sentiment_score = $row['sentiment_score'];
            $this->emotions = $row['emotions'];
            $this->key_phrases = $row['key_phrases'];
            $this->topics = $row['topics'];
            $this->analysis_summary = $row['analysis_summary'];
            return true;
        }
        return false;
    }

    // Update sentiment analysis
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET sentiment_label=:sentiment_label, 
                      sentiment_score=:sentiment_score, 
                      emotions=:emotions, 
                      key_phrases=:key_phrases, 
                      topics=:topics, 
                      analysis_summary=:analysis_summary 
                  WHERE audio_recording_id=:audio_recording_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':sentiment_label', $this->sentiment_label);
        $stmt->bindParam(':sentiment_score', $this->sentiment_score);
        $stmt->bindParam(':emotions', $this->emotions);
        $stmt->bindParam(':key_phrases', $this->key_phrases);
        $stmt->bindParam(':topics', $this->topics);
        $stmt->bindParam(':analysis_summary', $this->analysis_summary);
        $stmt->bindParam(':audio_recording_id', $this->audio_recording_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete sentiment analysis
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