<?php
require_once '../../config/config.php';
requireLogin();

// Only admin can access user management
if (getCurrentUserRole() !== 'admin') {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect(APP_URL . '/public/dashboard.php');
}

$pageTitle = 'Edit User';

// Get user ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('User not found', 'error');
    redirect(APP_URL . '/modules/auth/users.php');
}

$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $role = sanitize($_POST['role']);
    $status = sanitize($_POST['status']);
    $newPassword = $_POST['new_password'];

    $errors = [];

    // Check if email exists for other users
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = 'Email already exists';
    }
    $stmt->close();

    // Validate password if provided
    if (!empty($newPassword) && strlen($newPassword) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    // Prevent demoting yourself from admin
    if ($id === getCurrentUserId() && $role !== 'admin') {
        $errors[] = 'You cannot change your own admin role';
    }

    // Prevent disabling yourself
    if ($id === getCurrentUserId() && $status !== 'active') {
        $errors[] = 'You cannot disable your own account';
    }

    if (empty($errors)) {
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ?, status = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $fullName, $email, $phone, $role, $status, $hashedPassword, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $fullName, $email, $phone, $role, $status, $id);
        }

        if ($stmt->execute()) {
            logActivity($conn, getCurrentUserId(), 'Updated user: ' . $user['username'], 'users');
            setMessage('User updated successfully', 'success');
            redirect(APP_URL . '/modules/auth/view_user.php?id=' . $id);
        } else {
            setMessage('Error updating user: ' . $conn->error, 'error');
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
        <h3>Edit User</h3>
        <a href="view_user.php?id=<?php echo $id; ?>" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="userEditForm">
            <!-- Username (Read-only) -->
            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="full_name">Full Name <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email <span style="color: var(--danger-color);">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Leave blank to keep current password" minlength="6">
                <small style="color: var(--secondary-color);">Leave blank to keep the current password</small>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="role">Role <span style="color: var(--danger-color);">*</span></label>
                    <select id="role" name="role" class="form-control" required <?php echo $id === getCurrentUserId() ? 'disabled' : ''; ?>>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="veterinarian" <?php echo $user['role'] === 'veterinarian' ? 'selected' : ''; ?>>Veterinarian</option>
                        <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                        <option value="farm_owner" <?php echo $user['role'] === 'farm_owner' ? 'selected' : ''; ?>>Farm Owner</option>
                    </select>
                    <?php if ($id === getCurrentUserId()): ?>
                    <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                    <small style="color: var(--warning-color);">You cannot change your own role</small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required <?php echo $id === getCurrentUserId() ? 'disabled' : ''; ?>>
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    <?php if ($id === getCurrentUserId()): ?>
                    <input type="hidden" name="status" value="<?php echo $user['status']; ?>">
                    <small style="color: var(--warning-color);">You cannot disable your own account</small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="view_user.php?id=<?php echo $id; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
