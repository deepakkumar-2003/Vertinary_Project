<?php
require_once '../../config/config.php';
requireLogin();

// Only admin can access user management
if (getCurrentUserRole() !== 'admin') {
    setMessage('Access denied. Admin privileges required.', 'error');
    redirect(APP_URL . '/public/dashboard.php');
}

$pageTitle = 'User Management';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Prevent deleting yourself
    if ($id === getCurrentUserId()) {
        setMessage('You cannot delete your own account', 'error');
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            logActivity($conn, getCurrentUserId(), 'Deleted user ID: ' . $id, 'users');
            setMessage('User deleted successfully', 'success');
        } else {
            setMessage('Error deleting user', 'error');
        }
        $stmt->close();
    }
    redirect(APP_URL . '/modules/auth/users.php');
}

// Handle status toggle
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);

    // Prevent disabling yourself
    if ($id === getCurrentUserId()) {
        setMessage('You cannot disable your own account', 'error');
    } else {
        $conn->query("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = $id");
        logActivity($conn, getCurrentUserId(), 'Toggled user status ID: ' . $id, 'users');
        setMessage('User status updated', 'success');
    }
    redirect(APP_URL . '/modules/auth/users.php');
}

// Get search query
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? sanitize($_GET['role']) : '';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$recordsPerPage = RECORDS_PER_PAGE;
$offset = ($page - 1) * $recordsPerPage;

// Build query
$whereClause = "WHERE 1=1";
if (!empty($search)) {
    $whereClause .= " AND (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%')";
}
if (!empty($roleFilter)) {
    $whereClause .= " AND role = '$roleFilter'";
}

// Get total records
$totalQuery = "SELECT COUNT(*) as total FROM users $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get users
$query = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT $offset, $recordsPerPage";
$result = $conn->query($query);

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>User Management</h3>
        <a href="add_user.php" class="btn btn-primary btn-sm">+ Add User</a>
    </div>
    <div class="card-body">
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by username, name, or email..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <select name="role" class="form-control select-filter">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="veterinarian" <?php echo $roleFilter === 'veterinarian' ? 'selected' : ''; ?>>Veterinarian</option>
                    <option value="staff" <?php echo $roleFilter === 'staff' ? 'selected' : ''; ?>>Staff</option>
                    <option value="farm_owner" <?php echo $roleFilter === 'farm_owner' ? 'selected' : ''; ?>>Farm Owner</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($search) || !empty($roleFilter)): ?>
                <a href="users.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Info -->
        <?php if ($totalRecords > 0): ?>
        <p class="text-muted mb-20" style="font-size: var(--font-sm);">
            Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $recordsPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> users
        </p>
        <?php endif; ?>

        <!-- Users Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th class="hide-mobile">Email</th>
                        <th>Role</th>
                        <th class="hide-mobile">Last Login</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td class="hide-mobile"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
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
                            </td>
                            <td class="hide-mobile">
                                <?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?>
                            </td>
                            <td><?php echo getStatusBadge($user['status']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <?php if ($user['id'] !== getCurrentUserId()): ?>
                                    <a href="?toggle=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('Toggle user status?')"><?php echo $user['status'] === 'active' ? 'Disable' : 'Enable'; ?></a>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Are you sure you want to delete this user?')">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon">👥</div>
                                    <h4>No Users Found</h4>
                                    <p><?php echo empty($search) && empty($roleFilter) ? 'Start by adding your first user.' : 'Try adjusting your search terms.'; ?></p>
                                    <?php if (empty($search) && empty($roleFilter)): ?>
                                    <a href="add_user.php" class="btn btn-primary">Add User</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>">Previous</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);

            if ($start > 1): ?>
                <a href="?page=1&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>">1</a>
                <?php if ($start > 2): ?><span>...</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span>...</span><?php endif; ?>
                <a href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>"><?php echo $totalPages; ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
