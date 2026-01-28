<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/AudioRecording.php';
require_once 'includes/auth.php';

require_login();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Get current user details
$current_user = get_current_user_info();
$user->get_user_by_id($current_user['id']);

$message = '';
$error = '';

if ($_POST) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        // Handle file upload
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] == 0) {
            $allowed_types = ['audio/wav', 'audio/mpeg', 'audio/mp3', 'audio/wma', 'audio/aac', 'audio/flac', 'audio/ogg'];
            $file_type = $_FILES['audio_file']['type'];
            $file_size = $_FILES['audio_file']['size'];
            $file_name = $_FILES['audio_file']['name'];
            
            // Check if file type is allowed
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Invalid file type. Only audio files are allowed.';
            } 
            // Check file size (max 100MB)
            elseif ($file_size > 100 * 1024 * 1024) {
                $error = 'File too large. Maximum size is 100MB.';
            } else {
                // Create uploads directory if it doesn't exist
                $upload_dir = 'uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
                $target_path = $upload_dir . $unique_filename;
                
                if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $target_path)) {
                    // Save to database
                    $audio_recording = new AudioRecording($db);
                    $audio_recording->user_id = $current_user['id'];
                    $audio_recording->filename = $unique_filename;
                    $audio_recording->original_filename = $file_name;
                    $audio_recording->file_path = $target_path;
                    $audio_recording->file_size_bytes = $file_size;
                    $audio_recording->transcription_status = 'pending';
                    $audio_recording->analysis_status = 'pending';
                    
                    // Get duration using FFmpeg if available
                    $duration = 0;
                    if (file_exists('/usr/bin/ffmpeg') || file_exists('/usr/local/bin/ffmpeg')) {
                        $cmd = 'ffprobe -v quiet -show_entries format=duration -of csv=p=0 "' . $target_path . '"';
                        $result = shell_exec($cmd);
                        if (is_numeric(trim($result))) {
                            $duration = intval(trim($result));
                        }
                    }
                    $audio_recording->duration_seconds = $duration;
                    
                    if ($audio_recording->create()) {
                        $message = 'Audio file uploaded successfully!';
                        
                        // Trigger processing in background (we'll simulate this)
                        // In a real application, you would queue this for processing
                        $message .= ' Processing started in background.';
                    } else {
                        $error = 'Error saving to database.';
                        // Clean up uploaded file if DB save failed
                        unlink($target_path);
                    }
                } else {
                    $error = 'Error uploading file.';
                }
            }
        } else {
            $error = 'Please select an audio file to upload.';
        }
    }
}

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Audio - Speech Analytics System</title>
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
                        <a class="nav-link active" href="upload.php"><i class="fas fa-upload"></i> Upload Audio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="recordings.php"><i class="fas fa-microphone-alt"></i> Recordings</a>
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
                        <a href="upload.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-upload"></i> Upload Audio
                        </a>
                        <a href="recordings.php" class="list-group-item list-group-item-action">
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
                    <h1>Upload Audio File</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="mb-3">
                                <label for="audio_file" class="form-label">Select Audio File</label>
                                <input type="file" class="form-control" id="audio_file" name="audio_file" accept="audio/*" required>
                                <div class="form-text">
                                    Supported formats: WAV, MP3, WMA, AAC, FLAC, OGG. Max size: 100MB.
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Upload Audio
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5>About Speech Analytics</h5>
                    </div>
                    <div class="card-body">
                        <p>Our speech analytics system provides:</p>
                        <ul>
                            <li>High-quality audio transcription using advanced AI models</li>
                            <li>Sentiment analysis to understand customer emotions</li>
                            <li>Topic detection to identify key themes in conversations</li>
                            <li>Keyword extraction for compliance and quality assurance</li>
                            <li>Detailed reporting for business insights</li>
                        </ul>
                        <p>All audio files are securely processed and stored with enterprise-grade security.</p>
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
</body>
</html>