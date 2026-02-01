<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Diseases';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM diseases WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Deleted disease record ID: ' . $id, 'diseases');
        setMessage('Disease record deleted successfully', 'success');
    } else {
        setMessage('Error deleting disease record', 'error');
    }
    $stmt->close();
    redirect(APP_URL . '/modules/diseases/list.php');
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
    $whereClause .= " AND (d.disease_name LIKE '%$search%' OR a.animal_code LIKE '%$search%' OR a.name LIKE '%$search%' OR c.name LIKE '%$search%')";
}

// Get total records
$totalQuery = "SELECT COUNT(*) as total FROM diseases d
    LEFT JOIN animals a ON d.animal_id = a.id
    LEFT JOIN customers c ON a.customer_id = c.id
    $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get diseases
$query = "SELECT d.*, a.animal_code, a.name as animal_name, a.species,
    c.name as customer_name, u.full_name as diagnosed_by_name
    FROM diseases d
    LEFT JOIN animals a ON d.animal_id = a.id
    LEFT JOIN customers c ON a.customer_id = c.id
    LEFT JOIN users u ON d.diagnosed_by = u.id
    $whereClause
    ORDER BY d.created_at DESC
    LIMIT $offset, $recordsPerPage";

$result = $conn->query($query);

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Diseases Management</h3>
        <a href="add.php" class="btn btn-primary btn-sm">+ Add Disease Record</a>
    </div>
    <div class="card-body">
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by disease name, animal code, or customer..."
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
            Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $recordsPerPage, $totalRecords); ?> of <?php echo $totalRecords; ?> records
        </p>
        <?php endif; ?>

        <!-- Diseases Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Animal</th>
                        <th>Disease Name</th>
                        <th class="hide-mobile">Customer</th>
                        <th>Diagnosis Date</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($disease = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($disease['animal_code']); ?></strong>
                                <?php if ($disease['animal_name']): ?>
                                <br><small><?php echo htmlspecialchars($disease['animal_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($disease['disease_name']); ?></td>
                            <td class="hide-mobile"><?php echo htmlspecialchars($disease['customer_name'] ?: '-'); ?></td>
                            <td><?php echo formatDate($disease['diagnosis_date']); ?></td>
                            <td>
                                <?php
                                $severityClass = [
                                    'mild' => 'badge-success',
                                    'moderate' => 'badge-warning',
                                    'severe' => 'badge-danger',
                                    'critical' => 'badge-danger'
                                ];
                                $class = $severityClass[$disease['severity']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?php echo $class; ?>"><?php echo ucfirst($disease['severity']); ?></span>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'active' => 'badge-warning',
                                    'recovered' => 'badge-success',
                                    'chronic' => 'badge-danger'
                                ];
                                $class = $statusClass[$disease['status']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?php echo $class; ?>"><?php echo ucfirst($disease['status']); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view.php?id=<?php echo $disease['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <a href="edit.php?id=<?php echo $disease['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete=<?php echo $disease['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Are you sure you want to delete this disease record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon">🩺</div>
                                    <h4>No Disease Records Found</h4>
                                    <p><?php echo empty($search) ? 'Start by adding your first disease record.' : 'Try adjusting your search terms.'; ?></p>
                                    <?php if (empty($search)): ?>
                                    <a href="add.php" class="btn btn-primary">Add Disease Record</a>
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
