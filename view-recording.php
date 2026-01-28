<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/AudioRecording.php';
require_once 'classes/Transcription.php';
require_once 'classes/SentimentAnalysis.php';
require_once 'includes/auth.php';

require_login();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$audio_recording = new AudioRecording($db);
$transcription = new Transcription($db);
$sentiment_analysis = new SentimentAnalysis($db);

// Get current user details
$current_user = get_current_user_info();
$user->get_user_by_id($current_user['id']);

// Get recording ID from URL
$recording_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$recording_id) {
    header('Location: recordings.php');
    exit();
}

// Get recording details
if (!$audio_recording->get_by_id($recording_id)) {
    header('Location: recordings.php');
    exit();
}

// Check permissions - only owner, admin, or supervisor can view
if ($audio_recording->user_id != $current_user['id'] && !has_role('admin') && !has_role('supervisor')) {
    header('Location: recordings.php');
    exit();
}

// Get transcription if exists
$transcription_found = $transcription->get_by_audio_id($recording_id);

// Get sentiment analysis if exists
$analysis_found = $sentiment_analysis->get_by_audio_id($recording_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Recording - Speech Analytics System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Speech Analytics System</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="upload.php"><i class="fas fa-upload"></i> Upload Audio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="recordings.php"><i class="fas fa-microphone-alt"></i> Recordings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                    </li>
                    
                    <?php if (has_role('admin') || has_role('supervisor')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="users.php">Manage Users</a></li>
                            <?php if (has_role('admin')): ?>
                            <li><a class="dropdown-item" href="system-settings.php">System Settings</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="change-password.php"><i class="fas fa-lock"></i> Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5>Navigation</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="index.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="upload.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-upload"></i> Upload Audio
                        </a>
                        <a href="recordings.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-microphone-alt"></i> My Recordings
                        </a>
                        <a href="reports.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                        <?php if (has_role('admin') || has_role('supervisor')): ?>
                        <a href="users.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                        <?php endif; ?>
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Recording Details</h1>
                    <a href="recordings.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Recordings
                    </a>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Audio Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>ID:</th>
                                        <td><?php echo htmlspecialchars($audio_recording->id); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Original Filename:</th>
                                        <td><?php echo htmlspecialchars($audio_recording->original_filename); ?></td>
                                    </tr>
                                    <tr>
                                        <th>File Size:</th>
                                        <td><?php echo formatBytes($audio_recording->file_size_bytes); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Duration:</th>
                                        <td><?php echo $audio_recording->duration_seconds ? round($audio_recording->duration_seconds/60, 2) . ' minutes' : 'N/A'; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge bg-<?php echo getStatusBadgeClass($audio_recording->transcription_status); ?>">
                                                Transcription: <?php echo ucfirst(str_replace('_', ' ', $audio_recording->transcription_status)); ?>
                                            </span><br>
                                            <span class="badge bg-<?php echo getStatusBadgeClass($audio_recording->analysis_status); ?>">
                                                Analysis: <?php echo ucfirst(str_replace('_', ' ', $audio_recording->analysis_status)); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Uploaded Date:</th>
                                        <td><?php echo date('M j, Y g:i A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Actions:</th>
                                        <td>
                                            <a href="<?php echo $audio_recording->file_path; ?>" class="btn btn-sm btn-outline-primary" download>
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Audio Player -->
                        <div class="mt-4">
                            <audio controls style="width: 100%;">
                                <source src="<?php echo $audio_recording->file_path; ?>" type="audio/<?php echo strtolower(pathinfo($audio_recording->original_filename, PATHINFO_EXTENSION)); ?>">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                    </div>
                </div>

                <!-- Transcription Section -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Transcription</h5>
                        <span class="badge bg-<?php echo getStatusBadgeClass($audio_recording->transcription_status); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $audio_recording->transcription_status)); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if ($transcription_found): ?>
                            <div class="alert alert-info">
                                <strong>Confidence Score:</strong> <?php echo $transcription->confidence_score; ?>%
                            </div>
                            <div class="border p-3 bg-light rounded">
                                <pre class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($transcription->transcription_text); ?></pre>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <p>No transcription available yet. Processing may take a few moments.</p>
                                <?php if (has_role('admin') || has_role('supervisor')): ?>
                                    <button class="btn btn-primary" id="processTranscriptionBtn">
                                        <i class="fas fa-cogs"></i> Process Now
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sentiment Analysis Section -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Sentiment Analysis</h5>
                        <span class="badge bg-<?php echo getStatusBadgeClass($audio_recording->analysis_status); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $audio_recording->analysis_status)); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if ($analysis_found): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Sentiment</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <span class="badge fs-5 bg-<?php echo getSentimentColor($sentiment_analysis->sentiment_label); ?>">
                                                <?php echo ucfirst($sentiment_analysis->sentiment_label); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <strong>Score:</strong> <?php echo $sentiment_analysis->sentiment_score; ?>/10
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Emotions Detected</h6>
                                    <?php if ($sentiment_analysis->emotions): ?>
                                        <?php $emotions = json_decode($sentiment_analysis->emotions, true); ?>
                                        <?php foreach ($emotions as $emotion => $score): ?>
                                            <div class="mb-2">
                                                <span class="badge bg-secondary"><?php echo ucfirst($emotion); ?>:</span>
                                                <span class="ms-2"><?php echo round($score * 100, 1); ?>%</span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No emotions detected</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h6>Key Phrases</h6>
                                    <?php if ($sentiment_analysis->key_phrases): ?>
                                        <?php $phrases = json_decode($sentiment_analysis->key_phrases, true); ?>
                                        <?php foreach ($phrases as $phrase): ?>
                                            <span class="badge bg-info me-1 mb-1"><?php echo htmlspecialchars($phrase); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No key phrases extracted</p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <h6>Topics Identified</h6>
                                    <?php if ($sentiment_analysis->topics): ?>
                                        <?php $topics = json_decode($sentiment_analysis->topics, true); ?>
                                        <?php foreach ($topics as $topic): ?>
                                            <span class="badge bg-warning me-1 mb-1"><?php echo htmlspecialchars($topic); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No topics identified</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h6>Analysis Summary</h6>
                                <div class="border p-3 bg-light rounded">
                                    <p class="mb-0"><?php echo htmlspecialchars($sentiment_analysis->analysis_summary); ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-4">
                                <i class="fas fa-brain fa-3x text-muted mb-3"></i>
                                <p>No sentiment analysis available yet. Processing may take a few moments.</p>
                                <?php if (has_role('admin') || has_role('supervisor')): ?>
                                    <button class="btn btn-primary" id="processAnalysisBtn">
                                        <i class="fas fa-cogs"></i> Process Now
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-5 py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© 2026 Speech Analytics System | Secure Bank Analytics Platform</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simulate processing for demo purposes
        document.getElementById('processTranscriptionBtn')?.addEventListener('click', function() {
            alert('In a real implementation, this would trigger the transcription process. For this demo, processing is simulated.');
            // In a real application, you would make an AJAX call to trigger processing
        });
        
        document.getElementById('processAnalysisBtn')?.addEventListener('click', function() {
            alert('In a real implementation, this would trigger the sentiment analysis process. For this demo, processing is simulated.');
            // In a real application, you would make an AJAX call to trigger processing
        });
    </script>
</body>
</html>

<?php
// Helper function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'completed':
            return 'success';
        case 'processing':
            return 'warning';
        case 'failed':
            return 'danger';
        case 'pending':
        default:
            return 'secondary';
    }
}

// Helper function to format bytes
function formatBytes($size, $precision = 2) {
    if ($size > 0) {
        $size = $size / 1024; // Convert to KB
        
        if ($size > 1024) {
            $size = $size / 1024; // Convert to MB
            
            if ($size > 1024) {
                $size = $size / 1024; // Convert to GB
                return round($size, $precision) . ' GB';
            }
            
            return round($size, $precision) . ' MB';
        }
        
        return round($size, $precision) . ' KB';
    } else {
        return '0 KB';
    }
}

// Helper function to get sentiment color
function getSentimentColor($sentiment) {
    switch (strtolower($sentiment)) {
        case 'positive':
            return 'success';
        case 'negative':
            return 'danger';
        case 'neutral':
            return 'secondary';
        default:
            return 'secondary';
    }
}
?>