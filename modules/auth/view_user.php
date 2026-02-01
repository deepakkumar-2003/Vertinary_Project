<?php
require_once '../../config/config.php';
requireLogin();

// Only admin can access user management
if (getCurrentUserRole() !== 'admin') {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect(APP_URL . '/public/dashboard.php');
}

$pageTitle = 'View User';

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

// Get recent activity logs
$activityQuery = $conn->query("SELECT * FROM activity_logs WHERE user_id = $id ORDER BY created_at DESC LIMIT 10");

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>User Details</h3>
        <div class="btn-group">
            <a href="edit_user.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">Edit</a>
            <a href="users.php" class="btn btn-outline btn-sm">← Back to List</a>
        </div>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Username</div>
                <div class="detail-value"><strong><?php echo htmlspecialchars($user['username']); ?></strong></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Full Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Phone</div>
                <div class="detail-value"><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Role</div>
                <div class="detail-value">
                    <?php
                    $roleClass = [
                        'admin' => 'badge-danger',
                        'veterinarian' => 'badge-primary',
                        'staff' => 'badge-warning',
                        'farm_owner' => 'badge-success'
                    ];
                    $class = $roleClass[$user['role']] ?? 'badge-secondary';
                    ?>
                    <span class="badge <?php echo $class; ?>"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></span>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value"><?php echo getStatusBadge($user['status']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Last Login</div>
                <div class="detail-value"><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Created At</div>
                <div class="detail-value"><?php echo formatDateTime($user['created_at']); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <h3>Recent Activity</h3>
    </div>
    <div class="card-body">
        <?php if ($activityQuery->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($activity = $activityQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo formatDateTime($activity['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($activity['action']); ?></td>
                        <td><?php echo htmlspecialchars($activity['module'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($activity['ip_address'] ?: '-'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h4>No Activity Found</h4>
            <p>No recent activity logs for this user.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
