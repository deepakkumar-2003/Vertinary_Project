<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Insurance';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Check for existing claims
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM insurance_claims WHERE policy_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        setMessage('Cannot delete policy with existing claims', 'error');
    } else {
        $stmt = $conn->prepare("DELETE FROM insurance_policies WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            logActivity($conn, getCurrentUserId(), 'Deleted insurance policy ID: ' . $id, 'insurance');
            setMessage('Insurance policy deleted successfully', 'success');
        } else {
            setMessage('Error deleting insurance policy', 'error');
        }
    }
    $stmt->close();
    redirect(APP_URL . '/modules/insurance/list.php');
}

// Get search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$recordsPerPage = RECORDS_PER_PAGE;
$offset = ($page - 1) * $recordsPerPage;

// Build query
$whereClause = "WHERE 1=1";
if (!empty($search)) {
    $whereClause .= " AND (ip.policy_number LIKE '%$search%' OR ip.insurance_company LIKE '%$search%' OR c.name LIKE '%$search%' OR a.animal_code LIKE '%$search%')";
}
if (!empty($statusFilter)) {
    $whereClause .= " AND ip.status = '$statusFilter'";
}

// Get total records
$totalQuery = "SELECT COUNT(*) as total FROM insurance_policies ip
    LEFT JOIN customers c ON ip.customer_id = c.id
    LEFT JOIN animals a ON ip.animal_id = a.id
    $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get policies
$query = "SELECT ip.*, c.name as customer_name, c.customer_code,
    a.animal_code, a.name as animal_name
    FROM insurance_policies ip
    LEFT JOIN customers c ON ip.customer_id = c.id
    LEFT JOIN animals a ON ip.animal_id = a.id
    $whereClause
    ORDER BY ip.created_at DESC
    LIMIT $offset, $recordsPerPage";

$result = $conn->query($query);

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Insurance Policies</h3>
        <a href="add.php" class="btn btn-primary btn-sm">+ Add Policy</a>
    </div>
    <div class="card-body">
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by policy number, company, customer, or animal..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <select name="status" class="form-control select-filter">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="expired" <?php echo $statusFilter === 'expired' ? 'selected' : ''; ?>>Expired</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="claimed" <?php echo $statusFilter === 'claimed' ? 'selected' : ''; ?>>Claimed</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($search) || !empty($statusFilter)): ?>
                <a href="list.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Info -->
        <?php if ($totalRecords > 0): ?>
        <p class="text-muted mb-20" style="font-size: var(--font-sm);">
            Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $recordsPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> policies
        </p>
        <?php endif; ?>

        <!-- Policies Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Policy Number</th>
                        <th>Customer</th>
                        <th class="hide-mobile">Animal</th>
                        <th class="hide-mobile">Company</th>
                        <th>Coverage</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($policy = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($policy['policy_number']); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($policy['customer_name']); ?>
                                <br><small><?php echo htmlspecialchars($policy['customer_code']); ?></small>
                            </td>
                            <td class="hide-mobile">
                                <?php if ($policy['animal_code']): ?>
                                <?php echo htmlspecialchars($policy['animal_code']); ?>
                                <?php if ($policy['animal_name']): ?>
                                <br><small><?php echo htmlspecialchars($policy['animal_name']); ?></small>
                                <?php endif; ?>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td class="hide-mobile"><?php echo htmlspecialchars($policy['insurance_company']); ?></td>
                            <td><?php echo formatCurrency($policy['coverage_amount']); ?></td>
                            <td>
                                <?php echo formatDate($policy['end_date']); ?>
                                <?php if ($policy['status'] === 'active' && strtotime($policy['end_date']) < time()): ?>
                                <br><span class="badge badge-danger">Expired</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'active' => 'badge-success',
                                    'expired' => 'badge-warning',
                                    'cancelled' => 'badge-danger',
                                    'claimed' => 'badge-primary'
                                ];
                                $class = $statusClass[$policy['status']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?php echo $class; ?>"><?php echo ucfirst($policy['status']); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view.php?id=<?php echo $policy['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <a href="edit.php?id=<?php echo $policy['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete=<?php echo $policy['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Are you sure you want to delete this policy?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">🛡️</div>
                                    <h4>No Insurance Policies Found</h4>
                                    <p><?php echo empty($search) && empty($statusFilter) ? 'Start by adding your first insurance policy.' : 'Try adjusting your search terms.'; ?></p>
                                    <?php if (empty($search) && empty($statusFilter)): ?>
                                    <a href="add.php" class="btn btn-primary">Add Policy</a>
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
                <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>">Previous</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);

            if ($start > 1): ?>
                <a href="?page=1&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>">1</a>
                <?php if ($start > 2): ?><span>...</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span>...</span><?php endif; ?>
                <a href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>"><?php echo $totalPages; ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
