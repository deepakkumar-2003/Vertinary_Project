<?php
require_once '../../config/config.php';
requireLogin();

// Only admin can access user management
if (getCurrentUserRole() !== 'admin') {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect(APP_URL . '/public/dashboard.php');
}

$pageTitle = 'Add User';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $fullName = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $role = sanitize($_POST['role']);
    $status = sanitize($_POST['status']);

    $errors = [];

    // Validate username
    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = 'Username already exists';
    }
    $stmt->close();

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = 'Email already exists';
    }
    $stmt->close();

    // Validate password
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, phone, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $hashedPassword, $fullName, $email, $phone, $role, $status);

        if ($stmt->execute()) {
            logActivity($conn, getCurrentUserId(), 'Added new user: ' . $username, 'users');
            setMessage('User created successfully', 'success');
            redirect(APP_URL . '/modules/auth/users.php');
        } else {
            setMessage('Error creating user: ' . $conn->error, 'error');
        }

        $stmt->close();
    } else {
        setMessage(implode('<br>', $errors), 'error');
    }
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add New User</h3>
        <a href="users.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="userForm">
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="username">Username <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="username" name="username" class="form-control" required placeholder="Enter username" minlength="3">
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required placeholder="Enter full name">
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="email">Email <span style="color: var(--danger-color);">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="Enter email address">
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter phone number">
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="password">Password <span style="color: var(--danger-color);">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Enter password" minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span style="color: var(--danger-color);">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Confirm password">
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="role">Role <span style="color: var(--danger-color);">*</span></label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="veterinarian">Veterinarian</option>
                        <option value="staff" selected>Staff</option>
                        <option value="farm_owner">Farm Owner</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="users.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
