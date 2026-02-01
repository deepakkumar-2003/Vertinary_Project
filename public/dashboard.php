<?php
require_once '../config/config.php';
requireLogin();

$pageTitle = 'Dashboard';

// Get statistics
$stats = [];

// Total Customers
$result = $conn->query("SELECT COUNT(*) as count FROM customers WHERE status = 'active'");
$stats['total_customers'] = $result->fetch_assoc()['count'];

// Total Animals
$result = $conn->query("SELECT COUNT(*) as count FROM animals WHERE status = 'active'");
$stats['total_animals'] = $result->fetch_assoc()['count'];

// Upcoming Vaccinations (next 30 days)
$result = $conn->query("SELECT COUNT(*) as count FROM vaccinations
    WHERE next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND status = 'scheduled'");
$stats['upcoming_vaccinations'] = $result->fetch_assoc()['count'];

// Active AI Records (Confirmed Pregnancies)
$result = $conn->query("SELECT COUNT(*) as count FROM ai_records WHERE pregnancy_status = 'confirmed'");
$stats['active_pregnancies'] = $result->fetch_assoc()['count'];

// Today's Milk Production
$result = $conn->query("SELECT SUM(total_milk) as total FROM dairy_records WHERE record_date = CURDATE()");
$row = $result->fetch_assoc();
$stats['today_milk_production'] = $row['total'] ?? 0;

// Active Loans
$result = $conn->query("SELECT COUNT(*) as count FROM loans WHERE status = 'active'");
$stats['active_loans'] = $result->fetch_assoc()['count'];

// Recent Activities with Pagination
$activitiesPerPage = 10;
$activityPage = isset($_GET['activity_page']) ? max(1, (int)$_GET['activity_page']) : 1;
$activityOffset = ($activityPage - 1) * $activitiesPerPage;

// Get total count
$totalResult = $conn->query("SELECT COUNT(*) as total FROM activity_logs");
$totalActivities = $totalResult->fetch_assoc()['total'];
$totalActivityPages = ceil($totalActivities / $activitiesPerPage);

$recentActivities = [];
$result = $conn->query("SELECT al.*, u.full_name
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT $activitiesPerPage OFFSET $activityOffset");

while ($row = $result->fetch_assoc()) {
    $recentActivities[] = $row;
}

// Overdue Vaccinations
$overdueVaccinations = [];
$result = $conn->query("SELECT v.*, a.name as animal_name, a.animal_code, c.name as customer_name
    FROM vaccinations v
    JOIN animals a ON v.animal_id = a.id
    JOIN customers c ON a.customer_id = c.id
    WHERE v.next_due_date < CURDATE() AND v.status = 'scheduled'
    ORDER BY v.next_due_date ASC
    LIMIT 5");

while ($row = $result->fetch_assoc()) {
    $overdueVaccinations[] = $row;
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <span>👥</span>
        </div>
        <div class="stat-info">
            <h4>Total Customers</h4>
            <div class="value"><?php echo $stats['total_customers']; ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <span>🐄</span>
        </div>
        <div class="stat-info">
            <h4>Total Animals</h4>
            <div class="value"><?php echo $stats['total_animals']; ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <span>💉</span>
        </div>
        <div class="stat-info">
            <h4>Upcoming Vaccinations</h4>
            <div class="value"><?php echo $stats['upcoming_vaccinations']; ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">
            <span>🔬</span>
        </div>
        <div class="stat-info">
            <h4>Active Pregnancies</h4>
            <div class="value"><?php echo $stats['active_pregnancies']; ?></div>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon success">
            <span>🥛</span>
        </div>
        <div class="stat-info">
            <h4>Today's Milk Production</h4>
            <div class="value"><?php echo number_format($stats['today_milk_production'], 2); ?> L</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <span>💰</span>
        </div>
        <div class="stat-info">
            <h4>Active Loans</h4>
            <div class="value"><?php echo $stats['active_loans']; ?></div>
        </div>
    </div>
</div>

<!-- Overdue Vaccinations Alert -->
<?php if (count($overdueVaccinations) > 0): ?>
<div class="card">
    <div class="card-header">
        <h3>⚠️ Overdue Vaccinations</h3>
        <a href="<?php echo APP_URL; ?>/modules/vaccinations/list.php" class="btn btn-sm btn-danger">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Animal Code</th>
                        <th>Animal Name</th>
                        <th>Customer</th>
                        <th>Vaccine</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overdueVaccinations as $vaccination): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vaccination['animal_code']); ?></td>
                        <td><?php echo htmlspecialchars($vaccination['animal_name']); ?></td>
                        <td><?php echo htmlspecialchars($vaccination['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($vaccination['vaccine_name']); ?></td>
                        <td><?php echo formatDate($vaccination['next_due_date']); ?></td>
                        <td>
                            <span class="badge badge-danger">
                                <?php echo abs(getDaysUntil($vaccination['next_due_date'])); ?> days
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Activities -->
<div class="card">
    <div class="card-header">
        <h3>Recent Activities</h3>
    </div>
    <div class="card-body">
        <?php if (count($recentActivities) > 0): ?>
        <div class="table-responsive" style="max-height: 400px; overflow-x: auto; overflow-y: auto;">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentActivities as $activity): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($activity['action']); ?></td>
                        <td>
                            <?php if ($activity['module']): ?>
                                <span class="badge badge-primary"><?php echo htmlspecialchars($activity['module']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDateTime($activity['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination for Recent Activities -->
        <?php if ($totalActivityPages > 1): ?>
        <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 5px; margin-top: 15px; flex-wrap: wrap;">
            <?php if ($activityPage > 1): ?>
                <a href="?activity_page=1" class="btn btn-sm btn-secondary" style="padding: 5px 10px;">&laquo; First</a>
                <a href="?activity_page=<?php echo $activityPage - 1; ?>" class="btn btn-sm btn-secondary" style="padding: 5px 10px;">&lsaquo; Prev</a>
            <?php endif; ?>

            <?php
            $startPage = max(1, $activityPage - 2);
            $endPage = min($totalActivityPages, $activityPage + 2);

            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <?php if ($i == $activityPage): ?>
                    <span class="btn btn-sm btn-primary" style="padding: 5px 10px;"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?activity_page=<?php echo $i; ?>" class="btn btn-sm btn-secondary" style="padding: 5px 10px;"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($activityPage < $totalActivityPages): ?>
                <a href="?activity_page=<?php echo $activityPage + 1; ?>" class="btn btn-sm btn-secondary" style="padding: 5px 10px;">Next &rsaquo;</a>
                <a href="?activity_page=<?php echo $totalActivityPages; ?>" class="btn btn-sm btn-secondary" style="padding: 5px 10px;">Last &raquo;</a>
            <?php endif; ?>
        </div>
        <p style="text-align: center; margin-top: 10px; color: #666; font-size: 14px;">
            Page <?php echo $activityPage; ?> of <?php echo $totalActivityPages; ?> (<?php echo $totalActivities; ?> total activities)
        </p>
        <?php endif; ?>

        <?php else: ?>
        <p class="text-center text-muted">No recent activities</p>
        <?php endif; ?>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
