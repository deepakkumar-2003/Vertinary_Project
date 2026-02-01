<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'AI Records';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM ai_records WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Deleted AI record ID: ' . $id, 'ai');
        setMessage('AI record deleted successfully', 'success');
    } else {
        setMessage('Error deleting AI record', 'error');
    }
    $stmt->close();
    redirect(APP_URL . '/modules/ai/list.php');
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
    $whereClause .= " AND (ai.bull_id LIKE '%$search%' OR ai.bull_breed LIKE '%$search%' OR a.animal_code LIKE '%$search%' OR a.name LIKE '%$search%' OR c.name LIKE '%$search%')";
}

// Get total records
$totalQuery = "SELECT COUNT(*) as total FROM ai_records ai
    LEFT JOIN animals a ON ai.animal_id = a.id
    LEFT JOIN customers c ON a.customer_id = c.id
    $whereClause";
$totalResult = $conn->query($totalQuery);
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get AI records
$query = "SELECT ai.*, a.animal_code, a.name as animal_name, a.species,
    c.name as customer_name, u.full_name as performed_by_name
    FROM ai_records ai
    LEFT JOIN animals a ON ai.animal_id = a.id
    LEFT JOIN customers c ON a.customer_id = c.id
    LEFT JOIN users u ON ai.performed_by = u.id
    $whereClause
    ORDER BY ai.created_at DESC
    LIMIT $offset, $recordsPerPage";

$result = $conn->query($query);

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Artificial Insemination Records</h3>
        <a href="add.php" class="btn btn-primary btn-sm">+ Add AI Record</a>
    </div>
    <div class="card-body">
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by bull ID, breed, animal, or customer..."
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

        <!-- AI Records Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Animal</th>
                        <th>AI Date</th>
                        <th class="hide-mobile">Bull ID</th>
                        <th class="hide-mobile">Bull Breed</th>
                        <th>Pregnancy Status</th>
                        <th class="hide-mobile">Expected Delivery</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($ai = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($ai['animal_code']); ?></strong>
                                <?php if ($ai['animal_name']): ?>
                                <br><small><?php echo htmlspecialchars($ai['animal_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($ai['ai_date']); ?></td>
                            <td class="hide-mobile"><?php echo htmlspecialchars($ai['bull_id'] ?: '-'); ?></td>
                            <td class="hide-mobile"><?php echo htmlspecialchars($ai['bull_breed'] ?: '-'); ?></td>
                            <td>
                                <?php
                                $statusClass = [
                                    'not_confirmed' => 'badge-warning',
                                    'confirmed' => 'badge-success',
                                    'failed' => 'badge-danger',
                                    'delivered' => 'badge-primary'
                                ];
                                $statusLabels = [
                                    'not_confirmed' => 'Not Confirmed',
                                    'confirmed' => 'Confirmed',
                                    'failed' => 'Failed',
                                    'delivered' => 'Delivered'
                                ];
                                $class = $statusClass[$ai['pregnancy_status']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?php echo $class; ?>"><?php echo $statusLabels[$ai['pregnancy_status']] ?? ucfirst($ai['pregnancy_status']); ?></span>
                            </td>
                            <td class="hide-mobile"><?php echo $ai['expected_delivery_date'] ? formatDate($ai['expected_delivery_date']) : '-'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view.php?id=<?php echo $ai['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                    <a href="edit.php?id=<?php echo $ai['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete=<?php echo $ai['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Are you sure you want to delete this AI record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon">🔬</div>
                                    <h4>No AI Records Found</h4>
                                    <p><?php echo empty($search) ? 'Start by adding your first AI record.' : 'Try adjusting your search terms.'; ?></p>
                                    <?php if (empty($search)): ?>
                                    <a href="add.php" class="btn btn-primary">Add AI Record</a>
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
