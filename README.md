# Speech Analytics System with Generative AI

A comprehensive speech analytics platform built with PHP, MySQL, and Python that provides audio transcription, sentiment analysis, and reporting capabilities with role-based access control.

## Features

- **Multi-role Authentication**: Admin, Supervisor, User, and Client roles
- **Audio Processing**: Upload and process various audio formats
- **AI-Powered Transcription**: High-quality speech-to-text conversion
- **Sentiment Analysis**: Emotion detection and sentiment scoring
- **Reporting System**: Comprehensive analytics and reporting
- **Banking-Focused**: Designed for financial institution compliance and needs

## Architecture

- **Frontend**: PHP with Bootstrap UI
- **Backend**: PHP with MySQL database
- **AI Processing**: Python with TensorFlow/HuggingFace models
- **Authentication**: Role-based access control system

## Database Schema

The system uses the following tables:
- `users`: Stores user information and roles
- `audio_recordings`: Manages uploaded audio files
- `transcriptions`: Stores transcription results
- `sentiment_analysis`: Contains sentiment analysis data
- `reports`: Manages generated reports

## Installation

1. Clone the repository
2. Run the setup script:
```bash
./setup_and_run.sh
```

This will:
- Install required Python dependencies
- Set up the MySQL database
- Create necessary directories

## Usage

### Starting the Application

```bash
php -S localhost:8000
```

Then visit `http://localhost:8000` in your browser.

### Default Login Credentials

| Role       | Username     | Password |
|------------|--------------|----------|
| Admin      | admin        | password |
| Supervisor | supervisor   | password |
| User       | user         | password |
| Client     | client       | password |

### Audio Processing

Audio files can be processed either through the web interface or directly via the Python script:

```bash
python3 speech_analytics_processor.py /path/to/audio/file.mp3
```

## Security Features

- CSRF protection
- SQL injection prevention
- Session management
- Role-based access control
- Password hashing

## Technology Stack

- **PHP 7.4+**
- **MySQL 5.7+**
- **Python 3.7+**
- **Bootstrap 5**
- **HuggingFace Transformers**
- **Google Speech Recognition API**

## Project Structure

```
/workspace/
├── config/                 # Database configuration
├── classes/               # PHP model classes
├── includes/              # Common includes
├── assets/                # CSS, JS, images
│   └── css/
├── uploads/               # Audio files storage
├── speech_analytics_system.sql  # Database schema
├── speech_analytics_processor.py  # Python processing script
├── setup_and_run.sh       # Setup script
├── login.php              # Authentication
├── index.php              # Dashboard
├── upload.php             # Audio upload
├── recordings.php         # Audio management
├── view-recording.php     # Audio details
├── reports.php            # Reporting system
├── users.php              # User management
├── profile.php            # Profile management
└── change-password.php    # Password management
```

## Banking-Specific Features

- Secure handling of sensitive audio data
- Compliance-ready reporting
- Detailed audit trails
- Multi-level access control
- Data encryption ready

## AI Capabilities

- Advanced speech recognition
- Sentiment analysis with confidence scoring
- Emotion detection
- Keyword extraction
- Topic identification

## Extending the System

The modular design allows for easy extension:
- Add new AI models for better transcription
- Integrate with banking-specific compliance systems
- Add additional reporting types
- Extend with custom NLP features