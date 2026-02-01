<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Loans';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Check for existing payments
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM loan_payments WHERE loan_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        setMessage('Cannot delete loan with existing payments', 'error');
    } else {
        $stmt = $conn->prepare("DELETE FROM loans WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            logActivity($conn, getCurrentUserId(), 'Deleted loan ID: ' . $id, 'loans');
            setMessage('Loan deleted successfully', 'success');
        } else {
            setMessage('Error deleting loan', 'error');
        }
    }
    $stmt->close();
    redirect(APP_URL . '/modules/loans/list.php');
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
    $whereClause .= " AND (l.loan_number LIKE '%$search%' OR l.loan_type LIKE '%$search%' OR c.name LIKE '%$search%' OR c.customer_code LIKE '%$search%')";
}
if (!empty($statusFilter)) {
    $whereClause .= " AND l.status = '$statusFilter'";
}

// Get total records
$totalQuery = "SELECT COUNT(*) as total FROM loans l
    LEFT JOIN customers c ON l.customer_id = c.id
    $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get loans
$query = "SELECT l.*, c.name as customer_name, c.customer_code
    FROM loans l
    LEFT JOIN customers c ON l.customer_id = c.id
    $whereClause
    ORDER BY l.created_at DESC
    LIMIT $offset, $recordsPerPage";

$result = $conn->query($query);

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Loans Management</h3>
        <a href="add.php" class="btn btn-primary btn-sm">+ Add Loan</a>
    </div>
    <div class="card-body">
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by loan number, type, or customer..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <select name="status" class="form-control select-filter">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="overdue" <?php echo $statusFilter === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                    <option value="defaulted" <?php echo $statusFilter === 'defaulted' ? 'selected' : ''; ?>>Defaulted</option>
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
            Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $recordsPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> loans
        </p>
        <?php endif; ?>

        <!-- Loans Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Loan Number</th>
                        <th>Customer</th>
                        <th class="hide-mobile">Type</th>
                        <th>Amount</th>
                        <th class="hide-mobile">Due Date</th>
                        <th>Remaining</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($loan = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($loan['loan_number']); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($loan['customer_name']); ?>
                                <br><small><?php echo htmlspecialchars($loan['customer_code']); ?></small>
                            </td>
                            <td class="hide-mobile"><?php echo htmlspecialchars($loan['loan_type'] ?: '-'); ?></td>
                            <td><?php echo formatCurrency($loan['loan_amount']); ?></td>
                            <td class="hide-mobile"><?php echo formatDate($loan['due_date']); ?></td>
                            <td><?php echo formatCurrency($loan['remaining_amount']); ?></td>
                            <td>
                                <?php
                                $statusClass = [
                                    'active' => 'badge-primary',
                                    'paid' => 'badge-success',
                                    'overdue' => 'badge-warning',
                                    'defaulted' => 'badge-danger'
                                ];
                                $class = $statusClass[$loan['status']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?php echo $class; ?>"><?php echo ucfirst($loan['status']); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view.php?id=<?php echo $loan['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <a href="edit.php?id=<?php echo $loan['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete=<?php echo $loan['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Are you sure you want to delete this loan?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">💰</div>
                                    <h4>No Loans Found</h4>
                                    <p><?php echo empty($search) && empty($statusFilter) ? 'Start by adding your first loan.' : 'Try adjusting your search terms.'; ?></p>
                                    <?php if (empty($search) && empty($statusFilter)): ?>
                                    <a href="add.php" class="btn btn-primary">Add Loan</a>
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
