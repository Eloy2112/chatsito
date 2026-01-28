#!/bin/bash

echo "Setting up Speech Analytics System..."

# Create uploads directory
mkdir -p uploads

# Install Python dependencies
echo "Installing Python dependencies..."
pip install --upgrade pip
pip install speechrecognition pydub librosa numpy transformers torch mysql-connector-python

# Set up the database
echo "Setting up database..."
mysql -u root -e "DROP DATABASE IF EXISTS speech_analytics_system;"
mysql -u root -e "SOURCE /workspace/speech_analytics_system.sql;"

echo "Database setup completed!"

# Create a simple test audio file for demonstration
echo "Creating a test audio file (using sox if available)..."
if command -v sox &> /dev/null; then
    sox -n test_tone.wav synth 3 sine 440 vol 0.2
    echo "Test audio file created: test_tone.wav"
else
    echo "SoX not found. Skipping test audio file creation."
fi

echo "Setup completed!"
echo ""
echo "To run the web application, start your PHP server:"
echo "php -S localhost:8000"
echo ""
echo "To process an audio file manually:"
echo "python3 speech_analytics_processor.py <path_to_audio_file>"
echo ""
echo "Default login credentials:"
echo "Admin: admin / password"
echo "Supervisor: supervisor / password"
echo "User: user / password"
echo "Client: client / password"