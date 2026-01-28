<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'includes/auth.php';

require_login();
require_role(['admin', 'supervisor']);

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Get current user details
$current_user = get_current_user_info();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add_user' && has_role('admin')) {
            // Add new user
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $department = trim($_POST['department']);
            
            // Validation
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'Username, email, and password are required.';
            } else {
                // Check if user already exists
                $user->username = $username;
                if ($user->userExists()) {
                    $error = 'Username already exists.';
                } else {
                    // Create new user
                    $user->username = $username;
                    $user->email = $email;
                    $user->password = password_hash($password, PASSWORD_DEFAULT);
                    $user->role = $role;
                    $user->first_name = $first_name;
                    $user->last_name = $last_name;
                    $user->department = $department;
                    
                    if ($user->create()) {
                        $message = 'User created successfully!';
                    } else {
                        $error = 'Error creating user.';
                    }
                }
            }
        } elseif ($action === 'update_profile' && has_role('admin')) {
            // Update user profile
            $user_id = (int)$_POST['user_id'];
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $department = trim($_POST['department']);
            $role = $_POST['role'];
            
            if ($user->get_user_by_id($user_id)) {
                $user->first_name = $first_name;
                $user->last_name = $last_name;
                $user->department = $department;
                // Only admin can change roles
                if (has_role('admin')) {
                    $user->role = $role;
                }
                
                if ($user->update_profile()) {
                    $message = 'User updated successfully!';
                } else {
                    $error = 'Error updating user.';
                }
            } else {
                $error = 'User not found.';
            }
        }
    }
}

$csrf_token = generate_csrf_token();

// Get users by role
$admin_users = $user->get_users_by_role('admin');
$supervisor_users = $user->get_users_by_role('supervisor');
$user_users = $user->get_users_by_role('user');
$client_users = $user->get_users_by_role('client');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Speech Analytics System</title>
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
                        <a class="nav-link" href="recordings.php"><i class="fas fa-microphone-alt"></i> Recordings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                    </li>
                    
                    <?php if (has_role('admin') || has_role('supervisor')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="users.php">Manage Users</a></li>
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
                        <a href="recordings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-microphone-alt"></i> My Recordings
                        </a>
                        <a href="reports.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                        <a href="users.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Manage Users</h1>
                    <?php if (has_role('admin')): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                    <?php endif; ?>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Admins Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Administrators</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $admin_users->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (has_role('admin')): ?>
                                                <button class="btn btn-sm btn-outline-primary edit-user-btn" 
                                                        data-user-id="<?php echo $row['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                                        data-first-name="<?php echo htmlspecialchars($row['first_name']); ?>"
                                                        data-last-name="<?php echo htmlspecialchars($row['last_name']); ?>"
                                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                        data-department="<?php echo htmlspecialchars($row['department']); ?>"
                                                        data-role="<?php echo $row['role']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if ($admin_users->rowCount() === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No administrators found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Supervisors Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Supervisors</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $supervisor_users->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (has_role('admin')): ?>
                                                <button class="btn btn-sm btn-outline-primary edit-user-btn" 
                                                        data-user-id="<?php echo $row['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                                        data-first-name="<?php echo htmlspecialchars($row['first_name']); ?>"
                                                        data-last-name="<?php echo htmlspecialchars($row['last_name']); ?>"
                                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                        data-department="<?php echo htmlspecialchars($row['department']); ?>"
                                                        data-role="<?php echo $row['role']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if ($supervisor_users->rowCount() === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No supervisors found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Regular Users Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $user_users->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (has_role('admin')): ?>
                                                <button class="btn btn-sm btn-outline-primary edit-user-btn" 
                                                        data-user-id="<?php echo $row['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                                        data-first-name="<?php echo htmlspecialchars($row['first_name']); ?>"
                                                        data-last-name="<?php echo htmlspecialchars($row['last_name']); ?>"
                                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                        data-department="<?php echo htmlspecialchars($row['department']); ?>"
                                                        data-role="<?php echo $row['role']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if ($user_users->rowCount() === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No users found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Clients Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Clients</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $client_users->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (has_role('admin')): ?>
                                                <button class="btn btn-sm btn-outline-primary edit-user-btn" 
                                                        data-user-id="<?php echo $row['id']; ?>"
                                                        data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                                        data-first-name="<?php echo htmlspecialchars($row['first_name']); ?>"
                                                        data-last-name="<?php echo htmlspecialchars($row['last_name']); ?>"
                                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                        data-department="<?php echo htmlspecialchars($row['department']); ?>"
                                                        data-role="<?php echo $row['role']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if ($client_users->rowCount() === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No clients found</td>
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

    <!-- Add User Modal -->
    <?php if (has_role('admin')): ?>
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="add_user">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="newUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="newUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="newEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="newEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="newPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="newRole" class="form-label">Role</label>
                            <select class="form-select" id="newRole" name="role" required>
                                <option value="user">User</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Admin</option>
                                <option value="client">Client</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newFirstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="newFirstName" name="first_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newLastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="newLastName" name="last_name">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="newDepartment" class="form-label">Department</label>
                            <input type="text" class="form-control" id="newDepartment" name="department">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Edit User Modal -->
    <?php if (has_role('admin')): ?>
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" id="editUserId" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" name="username" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">Role</label>
                            <select class="form-select" id="editRole" name="role" required>
                                <option value="user">User</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Admin</option>
                                <option value="client">Client</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editFirstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editLastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="editLastName" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editDepartment" class="form-label">Department</label>
                            <input type="text" class="form-control" id="editDepartment" name="department">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <footer class="footer mt-5 py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">© 2026 Speech Analytics System | Secure Bank Analytics Platform</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit user button clicks
        document.querySelectorAll('.edit-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Fill the modal with user data
                document.getElementById('editUserId').value = this.dataset.userId;
                document.getElementById('editUsername').value = this.dataset.username;
                document.getElementById('editFirstName').value = this.dataset.firstName;
                document.getElementById('editLastName').value = this.dataset.lastName;
                document.getElementById('editEmail').value = this.dataset.email;
                document.getElementById('editDepartment').value = this.dataset.department;
                
                // Set the selected role
                const roleSelect = document.getElementById('editRole');
                roleSelect.value = this.dataset.role;
                
                // Show the modal
                const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                editModal.show();
            });
        });
    </script>
</body>
</html>