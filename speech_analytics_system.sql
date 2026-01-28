-- Database: speech_analytics_system
CREATE DATABASE IF NOT EXISTS speech_analytics_system;
USE speech_analytics_system;

-- Users table for authentication and roles
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'supervisor', 'user', 'client') DEFAULT 'user',
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    department VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Audio recordings table
CREATE TABLE audio_recordings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    duration_seconds INT,
    file_size_bytes BIGINT,
    transcription_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    analysis_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Transcriptions table
CREATE TABLE transcriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audio_recording_id INT NOT NULL,
    transcription_text TEXT,
    confidence_score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (audio_recording_id) REFERENCES audio_recordings(id) ON DELETE CASCADE
);

-- Sentiment analysis table
CREATE TABLE sentiment_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audio_recording_id INT NOT NULL,
    sentiment_label VARCHAR(20), -- positive, negative, neutral
    sentiment_score DECIMAL(5,2),
    emotions JSON, -- store emotion scores as JSON
    key_phrases JSON, -- key phrases identified in the audio
    topics JSON, -- topics detected in the audio
    analysis_summary TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (audio_recording_id) REFERENCES audio_recordings(id) ON DELETE CASCADE
);

-- Reports table
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    report_type ENUM('daily', 'weekly', 'monthly', 'custom') DEFAULT 'custom',
    generated_by INT NOT NULL,
    filters JSON, -- store filter criteria as JSON
    report_data JSON, -- store report data as JSON
    report_file_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, email, password_hash, role, first_name, last_name) VALUES
('admin', 'admin@speechanalytics.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator'),
('supervisor', 'supervisor@speechanalytics.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor', 'John', 'Supervisor'),
('user', 'user@speechanalytics.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Jane', 'Agent'),
('client', 'client@speechanalytics.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'Bob', 'Client');

-- Indexes for better performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_audio_recordings_user_id ON audio_recordings(user_id);
CREATE INDEX idx_audio_recordings_created_at ON audio_recordings(created_at);
CREATE INDEX idx_transcriptions_audio_id ON transcriptions(audio_recording_id);
CREATE INDEX idx_sentiment_audio_id ON sentiment_analysis(audio_recording_id);
CREATE INDEX idx_reports_generated_by ON reports(generated_by);
CREATE INDEX idx_reports_created_at ON reports(created_at);