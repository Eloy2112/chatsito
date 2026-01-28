#!/usr/bin/env python3
"""
Speech Analytics Processor
This script handles audio transcription and sentiment analysis using AI models.
"""

import os
import sys
import json
import logging
import subprocess
from datetime import datetime
from typing import Dict, Any, Optional

try:
    import speech_recognition as sr
    from pydub import AudioSegment
    import librosa
    import numpy as np
    from transformers import pipeline, AutoTokenizer, AutoModelForSequenceClassification
except ImportError as e:
    print(f"Missing required packages: {e}")
    print("Install required packages: pip install speechrecognition pydub librosa numpy transformers torch")
    sys.exit(1)

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('speech_analytics.log'),
        logging.StreamHandler()
    ]
)

class SpeechAnalyticsProcessor:
    def __init__(self):
        self.r = sr.Recognizer()
        self.transcriber = None
        self.sentiment_analyzer = None
        self.setup_models()
        
    def setup_models(self):
        """Initialize AI models for transcription and sentiment analysis"""
        try:
            # Initialize Hugging Face sentiment analysis model
            self.sentiment_analyzer = pipeline(
                "sentiment-analysis",
                model="nlptown/bert-base-multilingual-uncased-sentiment",
                tokenizer="nlptown/bert-base-multilingual-uncased-sentiment"
            )
            logging.info("Sentiment analysis model loaded successfully")
        except Exception as e:
            logging.error(f"Failed to load sentiment analysis model: {e}")
            
        try:
            # Initialize speech recognition model
            # Using Google Web Speech API (requires internet)
            # Alternative: Whisper model for offline processing
            pass
        except Exception as e:
            logging.error(f"Failed to initialize speech recognition: {e}")

    def convert_audio_format(self, input_path: str, output_path: str = None) -> str:
        """
        Convert audio to WAV format at 16kHz sample rate for better processing
        """
        if output_path is None:
            base_name = os.path.splitext(input_path)[0]
            output_path = f"{base_name}_converted.wav"
        
        try:
            # Load audio file with pydub
            audio = AudioSegment.from_file(input_path)
            
            # Convert to mono and resample to 16kHz
            audio = audio.set_frame_rate(16000).set_channels(1)
            
            # Export as WAV
            audio.export(output_path, format="wav")
            logging.info(f"Converted audio to: {output_path}")
            return output_path
        except Exception as e:
            logging.error(f"Failed to convert audio: {e}")
            raise

    def transcribe_audio(self, audio_path: str) -> Dict[str, Any]:
        """
        Transcribe audio file to text
        """
        try:
            # Convert audio to compatible format if needed
            converted_path = self.convert_audio_format(audio_path)
            
            # Load audio file for transcription
            with sr.AudioFile(converted_path) as source:
                audio_data = self.r.record(source)
                
            # Attempt transcription using Google Web Speech API
            try:
                text = self.r.recognize_google(audio_data, language="en-US")
                confidence = 0.9  # Google API doesn't return confidence score directly
            except sr.UnknownValueError:
                logging.warning("Could not understand audio")
                text = ""
                confidence = 0.0
            except sr.RequestError as e:
                logging.error(f"Could not request results from Google Speech Recognition service; {e}")
                text = ""
                confidence = 0.0
            
            # Clean up converted file
            if os.path.exists(converted_path) and converted_path != audio_path:
                os.remove(converted_path)
                
            return {
                'transcription_text': text,
                'confidence_score': confidence
            }
        except Exception as e:
            logging.error(f"Transcription failed: {e}")
            return {
                'transcription_text': '',
                'confidence_score': 0.0
            }

    def analyze_sentiment(self, text: str) -> Dict[str, Any]:
        """
        Analyze sentiment of the transcribed text
        """
        if not text.strip():
            return {
                'sentiment_label': 'neutral',
                'sentiment_score': 0.0,
                'emotions': json.dumps({}),
                'key_phrases': json.dumps([]),
                'topics': json.dumps([]),
                'analysis_summary': 'No text to analyze'
            }
        
        try:
            # Analyze sentiment
            sentiment_result = self.sentiment_analyzer(text[:512])[0]  # Limit text length
            
            # Extract sentiment label and score
            sentiment_label = sentiment_result['label'].lower()
            sentiment_score = float(sentiment_result['score'])
            
            # Simple emotion detection based on sentiment
            emotions = {
                'positive': sentiment_score if 'pos' in sentiment_label else 1 - sentiment_score,
                'negative': sentiment_score if 'neg' in sentiment_label else 1 - sentiment_score,
                'neutral': 0.5  # Placeholder
            }
            
            # Extract key phrases (simplified approach)
            key_phrases = self.extract_key_phrases(text)
            
            # Identify topics (simplified approach)
            topics = self.extract_topics(text)
            
            # Generate analysis summary
            summary = self.generate_summary(sentiment_label, sentiment_score, key_phrases)
            
            return {
                'sentiment_label': sentiment_label,
                'sentiment_score': sentiment_score,
                'emotions': json.dumps(emotions),
                'key_phrases': json.dumps(key_phrases),
                'topics': json.dumps(topics),
                'analysis_summary': summary
            }
        except Exception as e:
            logging.error(f"Sentiment analysis failed: {e}")
            return {
                'sentiment_label': 'neutral',
                'sentiment_score': 0.0,
                'emotions': json.dumps({}),
                'key_phrases': json.dumps([]),
                'topics': json.dumps([]),
                'analysis_summary': 'Error analyzing sentiment'
            }

    def extract_key_phrases(self, text: str) -> list:
        """
        Extract key phrases from text (simplified implementation)
        """
        # This is a simplified implementation
        # In a production system, use NLP libraries like spaCy or NLTK
        words = text.split()
        key_phrases = []
        
        # Look for capitalized words which might indicate important terms
        for i, word in enumerate(words):
            if word[0].isupper() and len(word) > 2:
                phrase = word
                # Check if next word is also capitalized (potential phrase)
                if i + 1 < len(words) and words[i + 1][0].isupper():
                    phrase += " " + words[i + 1]
                if phrase.lower() not in ['the', 'and', 'for', 'are', 'but', 'not', 'you', 'all']:
                    key_phrases.append(phrase)
        
        return list(set(key_phrases))[:10]  # Return top 10 unique phrases

    def extract_topics(self, text: str) -> list:
        """
        Extract topics from text (simplified implementation)
        """
        # This is a simplified implementation
        # In a production system, use topic modeling algorithms
        common_topics = [
            'banking', 'finance', 'customer service', 'account', 'loan', 
            'credit', 'payment', 'balance', 'transaction', 'interest',
            'complaint', 'satisfaction', 'product', 'service', 'issue'
        ]
        
        text_lower = text.lower()
        detected_topics = []
        
        for topic in common_topics:
            if topic in text_lower:
                detected_topics.append(topic)
        
        return list(set(detected_topics))

    def generate_summary(self, sentiment_label: str, sentiment_score: float, key_phrases: list) -> str:
        """
        Generate a summary of the analysis
        """
        phrase_str = ", ".join(key_phrases[:3]) if key_phrases else "no significant phrases"
        return f"The conversation had a {sentiment_label} sentiment with a confidence of {sentiment_score:.2f}. Key phrases identified: {phrase_str}."

    def process_audio_file(self, audio_path: str, db_connection_params: Dict[str, Any]):
        """
        Complete processing pipeline: transcription -> sentiment analysis -> database storage
        """
        try:
            logging.info(f"Starting processing for audio file: {audio_path}")
            
            # 1. Transcribe audio
            logging.info("Starting transcription...")
            transcription_result = self.transcribe_audio(audio_path)
            logging.info(f"Transcription completed with confidence: {transcription_result['confidence_score']}")
            
            # 2. Analyze sentiment
            logging.info("Starting sentiment analysis...")
            sentiment_result = self.analyze_sentiment(transcription_result['transcription_text'])
            logging.info(f"Sentiment analysis completed: {sentiment_result['sentiment_label']}")
            
            # 3. Store results in database (simulated)
            # In a real implementation, connect to the database and store results
            self.store_results_in_db(
                audio_path, 
                transcription_result, 
                sentiment_result, 
                db_connection_params
            )
            
            logging.info("Processing completed successfully")
            return {
                'success': True,
                'transcription': transcription_result,
                'analysis': sentiment_result
            }
        except Exception as e:
            logging.error(f"Processing failed: {e}")
            return {
                'success': False,
                'error': str(e)
            }

    def store_results_in_db(self, audio_path: str, transcription_result: Dict[str, Any], 
                          sentiment_result: Dict[str, Any], db_params: Dict[str, Any]):
        """
        Store results in the database (simulated)
        """
        # In a real implementation, connect to the database and store results
        # This would involve using the MySQL credentials provided in db_params
        logging.info("Storing results in database...")
        
        # Simulate database storage
        # 1. Find the corresponding audio recording in the database
        # 2. Insert/update transcription record
        # 3. Insert/update sentiment analysis record
        # 4. Update status in audio_recordings table


def main():
    if len(sys.argv) < 2:
        print("Usage: python speech_analytics_processor.py <audio_file_path>")
        print("Optional: Provide database connection parameters as JSON string")
        sys.exit(1)
    
    audio_file_path = sys.argv[1]
    
    # Default database params (would come from config in real app)
    db_params = {
        'host': 'localhost',
        'database': 'speech_analytics_system',
        'user': 'root',
        'password': ''
    }
    
    # If provided, override db params from command line
    if len(sys.argv) > 2:
        try:
            custom_db_params = json.loads(sys.argv[2])
            db_params.update(custom_db_params)
        except json.JSONDecodeError:
            print("Invalid JSON for database parameters")
            sys.exit(1)
    
    # Initialize processor
    processor = SpeechAnalyticsProcessor()
    
    # Process the audio file
    result = processor.process_audio_file(audio_file_path, db_params)
    
    if result['success']:
        print("Processing completed successfully!")
        print(json.dumps(result, indent=2))
    else:
        print(f"Processing failed: {result['error']}")
        sys.exit(1)


if __name__ == "__main__":
    main()