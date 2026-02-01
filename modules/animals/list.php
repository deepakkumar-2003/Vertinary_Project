<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Animals';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM animals WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Deleted animal ID: ' . $id, 'animals');
        setMessage('Animal deleted successfully', 'success');
    } else {
        setMessage('Error deleting animal', 'error');
    }
    $stmt->close();
    redirect(APP_URL . '/modules/animals/list.php');
}

// Get search query
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$recordsPerPage = RECORDS_PER_PAGE;
$offset = ($page - 1) * $recordsPerPage;

// Build query
$whereClause = "WHERE 1=1";
if (!empty($search)) {
    $whereClause .= " AND (a.animal_code LIKE '%$search%' OR a.name LIKE '%$search%' OR c.name LIKE '%$search%')";
}
if ($customer_id > 0) {
    $whereClause .= " AND a.customer_id = $customer_id";
}

// Get total records
$totalQuery = "SELECT COUNT(*) as total FROM animals a
    JOIN customers c ON a.customer_id = c.id
    $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get animals
$query = "SELECT a.*, c.name as customer_name, c.customer_code
    FROM animals a
    JOIN customers c ON a.customer_id = c.id
    $whereClause
    ORDER BY a.created_at DESC
    LIMIT $offset, $recordsPerPage";

$result = $conn->query($query);

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Animals Management</h3>
        <a href="add.php" class="btn btn-primary btn-sm">+ Add Animal</a>
    </div>
    <div class="card-body">
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by animal code, name, or customer..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($search) || $customer_id > 0): ?>
                <a href="list.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Info -->
        <?php if ($totalRecords > 0): ?>
        <p class="text-muted mb-20" style="font-size: var(--font-sm);">
            Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $recordsPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> animals
        </p>
        <?php endif; ?>

        <!-- Animals Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Animal Code</th>
                        <th>Name</th>
                        <th>Customer</th>
                        <th class="hide-mobile">Species</th>
                        <th class="hide-mobile">Breed</th>
                        <th class="hide-mobile">Gender</th>
                        <th>Age</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($animal = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($animal['animal_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($animal['name'] ?: '-'); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/modules/customers/view.php?id=<?php echo $animal['customer_id']; ?>" style="color: var(--primary-color);">
                                    <?php echo htmlspecialchars($animal['customer_name']); ?>
                                </a>
                            </td>
                            <td class="hide-mobile"><?php echo ucfirst($animal['species']); ?></td>
                            <td class="hide-mobile"><?php echo htmlspecialchars($animal['breed'] ?: '-'); ?></td>
                            <td class="hide-mobile"><?php echo ucfirst($animal['gender']); ?></td>
                            <td>
                                <?php
                                if ($animal['date_of_birth']) {
                                    echo calculateAge($animal['date_of_birth']);
                                } else {
                                    echo $animal['age_years'] ? $animal['age_years'] . 'y' : '-';
                                }
                                ?>
                            </td>
                            <td><?php echo getStatusBadge($animal['status']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view.php?id=<?php echo $animal['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <a href="edit.php?id=<?php echo $animal['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete=<?php echo $animal['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Are you sure you want to delete this animal?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon">🐄</div>
                                    <h4>No Animals Found</h4>
                                    <p><?php echo empty($search) ? 'Start by registering your first animal.' : 'Try adjusting your search terms.'; ?></p>
                                    <?php if (empty($search)): ?>
                                    <a href="add.php" class="btn btn-primary">Add Animal</a>
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
