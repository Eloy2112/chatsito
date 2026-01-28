<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'includes/auth.php';

require_login();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Get current user details
$current_user = get_current_user_info();
$user->get_user_by_id($current_user['id']);

// Get counts for dashboard stats
$audio_count_query = "SELECT COUNT(*) as count FROM audio_recordings";
$transcription_count_query = "SELECT COUNT(*) as count FROM transcriptions";
$analysis_count_query = "SELECT COUNT(*) as count FROM sentiment_analysis";

$audio_stmt = $db->query($audio_count_query);
$transcription_stmt = $db->query($transcription_count_query);
$analysis_stmt = $db->query($analysis_count_query);

$audio_count = $audio_stmt->fetch(PDO::FETCH_ASSOC)['count'];
$transcription_count = $transcription_stmt->fetch(PDO::FETCH_ASSOC)['count'];
$analysis_count = $analysis_stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent recordings
$recent_recordings_query = "SELECT ar.*, u.username, u.first_name, u.last_name 
                           FROM audio_recordings ar 
                           JOIN users u ON ar.user_id = u.id 
                           ORDER BY ar.created_at DESC 
                           LIMIT 5";
$recent_recordings_stmt = $db->query($recent_recordings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Speech Analytics System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
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
                        <a class="nav-link active" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="upload.php"><i class="fas fa-upload"></i> Upload Audio</a>
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

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5>Navigation</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="index.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="upload.php" class="list-group-item list-group-item-action">
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

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Dashboard</h1>
                    <div>Welcome back, <strong><?php echo htmlspecialchars($current_user['first_name']); ?></strong>!</div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $audio_count; ?></h4>
                                        <p class="card-text">Total Recordings</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-microphone fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $transcription_count; ?></h4>
                                        <p class="card-text">Transcriptions</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-file-alt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $analysis_count; ?></h4>
                                        <p class="card-text">Analyses Completed</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Recordings -->
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Recordings</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Filename</th>
                                        <th>User</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $recent_recordings_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['original_filename']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo $row['duration_seconds'] ? round($row['duration_seconds']/60, 2) . ' min' : 'N/A'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['transcription_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $row['transcription_status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="view-recording.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if ($recent_recordings_stmt->rowCount() === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No recordings found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <a href="recordings.php" class="btn btn-primary">View All Recordings</a>
                        </div>
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