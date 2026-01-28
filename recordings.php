<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/AudioRecording.php';
require_once 'includes/auth.php';

require_login();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$audio_recording = new AudioRecording($db);

// Get current user details
$current_user = get_current_user_info();
$user->get_user_by_id($current_user['id']);

// Determine which recordings to show based on role
if (has_role('admin') || has_role('supervisor')) {
    $recordings = $audio_recording->get_all_recordings(100);
} else {
    $recordings = $audio_recording->get_all_by_user($current_user['id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Recordings - Speech Analytics System</title>
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
                    <h1>My Recordings</h1>
                    <a href="upload.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Upload New Recording
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Filename</th>
                                        <th>User</th>
                                        <th>Duration</th>
                                        <th>Size</th>
                                        <th>Transcription Status</th>
                                        <th>Analysis Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $recordings->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['original_filename']); ?></td>
                                        <td>
                                            <?php if (has_role('admin') || has_role('supervisor')): ?>
                                                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                            <?php else: ?>
                                                You
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $row['duration_seconds'] ? round($row['duration_seconds']/60, 2) . ' min' : 'N/A'; ?></td>
                                        <td><?php echo formatBytes($row['file_size_bytes']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo getStatusBadgeClass($row['transcription_status']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $row['transcription_status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo getStatusBadgeClass($row['analysis_status']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $row['analysis_status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="view-recording.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <?php if (has_role('admin')): ?>
                                                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?php echo $row['id']; ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if ($recordings->rowCount() === 0): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No recordings found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this recording? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
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
        // Helper function to format bytes to human readable format
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        // Add event listeners to delete buttons
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const recordingId = this.getAttribute('data-id');
                document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                    // Here you would make an AJAX request to delete the recording
                    // For now we'll just reload the page after a mock deletion
                    window.location.href = 'delete-recording.php?id=' + recordingId;
                });
                
                // Show the modal
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
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
?>