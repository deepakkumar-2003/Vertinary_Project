<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Customers';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Check if customer has associated animals
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM animals WHERE customer_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        setMessage('Cannot delete customer with associated animals', 'error');
    } else {
        $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            logActivity($conn, getCurrentUserId(), 'Deleted customer ID: ' . $id, 'customers');
            setMessage('Customer deleted successfully', 'success');
        } else {
            setMessage('Error deleting customer', 'error');
        }
    }
    $stmt->close();
    redirect(APP_URL . '/modules/customers/list.php');
}

// Get search query
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$recordsPerPage = RECORDS_PER_PAGE;
$offset = ($page - 1) * $recordsPerPage;

// Build query
$whereClause = "WHERE 1=1";
if (!empty($search)) {
    $whereClause .= " AND (c.customer_code LIKE '%$search%' OR c.name LIKE '%$search%' OR c.phone LIKE '%$search%' OR c.email LIKE '%$search%')";
}

// Get total records
$totalQuery = "SELECT COUNT(*) as total FROM customers c $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get customers
$query = "SELECT c.*, u.full_name as created_by_name,
    (SELECT COUNT(*) FROM animals WHERE customer_id = c.id) as total_animals
    FROM customers c
    LEFT JOIN users u ON c.created_by = u.id
    $whereClause
    ORDER BY c.created_at DESC
    LIMIT $offset, $recordsPerPage";

$result = $conn->query($query);

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Customers Management</h3>
        <a href="add.php" class="btn btn-primary btn-sm">+ Add Customer</a>
    </div>
    <div class="card-body">
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by code, name, phone, or email..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($search)): ?>
                <a href="list.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Info -->
        <?php if ($totalRecords > 0): ?>
        <p class="text-muted mb-20" style="font-size: var(--font-sm);">
            Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $recordsPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> customers
        </p>
        <?php endif; ?>

        <!-- Customers Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Customer Code</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th class="hide-mobile">Email</th>
                        <th class="hide-mobile">City</th>
                        <th>Animals</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($customer = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($customer['customer_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td class="hide-mobile"><?php echo htmlspecialchars($customer['email'] ?: '-'); ?></td>
                            <td class="hide-mobile"><?php echo htmlspecialchars($customer['city'] ?: '-'); ?></td>
                            <td>
                                <span class="badge badge-primary"><?php echo $customer['total_animals']; ?></span>
                            </td>
                            <td><?php echo getStatusBadge($customer['status']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Are you sure you want to delete this customer?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">👥</div>
                                    <h4>No Customers Found</h4>
                                    <p><?php echo empty($search) ? 'Start by adding your first customer.' : 'Try adjusting your search terms.'; ?></p>
                                    <?php if (empty($search)): ?>
                                    <a href="add.php" class="btn btn-primary">Add Customer</a>
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
                <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
            <?php endif; ?>

            <?php
            // Show limited page numbers on mobile
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);

            if ($start > 1): ?>
                <a href="?page=1&search=<?php echo urlencode($search); ?>">1</a>
                <?php if ($start > 2): ?><span>...</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span>...</span><?php endif; ?>
                <a href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>"><?php echo $totalPages; ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
