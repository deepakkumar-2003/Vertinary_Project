<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'View Disease Record';

// Get disease ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get disease details
$stmt = $conn->prepare("SELECT d.*, a.animal_code, a.name as animal_name, a.species, a.id as animal_id,
    c.name as customer_name, c.id as customer_id, u.full_name as diagnosed_by_name
    FROM diseases d
    LEFT JOIN animals a ON d.animal_id = a.id
    LEFT JOIN customers c ON a.customer_id = c.id
    LEFT JOIN users u ON d.diagnosed_by = u.id
    WHERE d.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Disease record not found', 'error');
    redirect(APP_URL . '/modules/diseases/list.php');
}

$disease = $result->fetch_assoc();
$stmt->close();

// Get related treatments
$treatmentsQuery = $conn->query("SELECT t.*, u.full_name as prescribed_by_name
    FROM treatments t
    LEFT JOIN users u ON t.prescribed_by = u.id
    WHERE t.disease_id = $id
    ORDER BY t.treatment_date DESC");

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Disease Record Details</h3>
        <div class="btn-group">
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">Edit</a>
            <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
        </div>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Animal</div>
                <div class="detail-value">
                    <a href="<?php echo APP_URL; ?>/modules/animals/view.php?id=<?php echo $disease['animal_id']; ?>">
                        <strong><?php echo htmlspecialchars($disease['animal_code']); ?></strong>
                        <?php if ($disease['animal_name']): ?>
                        - <?php echo htmlspecialchars($disease['animal_name']); ?>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Species</div>
                <div class="detail-value"><?php echo ucfirst($disease['species']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Customer</div>
                <div class="detail-value">
                    <a href="<?php echo APP_URL; ?>/modules/customers/view.php?id=<?php echo $disease['customer_id']; ?>">
                        <?php echo htmlspecialchars($disease['customer_name']); ?>
                    </a>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Disease Name</div>
                <div class="detail-value"><strong><?php echo htmlspecialchars($disease['disease_name']); ?></strong></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Diagnosis Date</div>
                <div class="detail-value"><?php echo formatDate($disease['diagnosis_date']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Diagnosed By</div>
                <div class="detail-value"><?php echo htmlspecialchars($disease['diagnosed_by_name'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Severity</div>
                <div class="detail-value">
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
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <?php
                    $statusClass = [
                        'active' => 'badge-warning',
                        'recovered' => 'badge-success',
                        'chronic' => 'badge-danger'
                    ];
                    $class = $statusClass[$disease['status']] ?? 'badge-secondary';
                    ?>
                    <span class="badge <?php echo $class; ?>"><?php echo ucfirst($disease['status']); ?></span>
                </div>
            </div>
        </div>

        <?php if ($disease['symptoms']): ?>
        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Symptoms</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($disease['symptoms'])); ?></div>
        </div>
        <?php endif; ?>

        <?php if ($disease['notes']): ?>
        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Notes</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($disease['notes'])); ?></div>
        </div>
        <?php endif; ?>

        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Created At</div>
            <div class="detail-value"><?php echo formatDateTime($disease['created_at']); ?></div>
        </div>
    </div>
</div>

<!-- Related Treatments -->
<div class="card">
    <div class="card-header">
        <h3>Treatments (<?php echo $treatmentsQuery->num_rows; ?>)</h3>
        <a href="<?php echo APP_URL; ?>/modules/treatments/add.php?disease_id=<?php echo $id; ?>&animal_id=<?php echo $disease['animal_id']; ?>" class="btn btn-primary btn-sm">+ Add Treatment</a>
    </div>
    <div class="card-body">
        <?php if ($treatmentsQuery->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Treatment Type</th>
                        <th>Medicine</th>
                        <th>Dosage</th>
                        <th>Prescribed By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($treatment = $treatmentsQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo formatDate($treatment['treatment_date']); ?></td>
                        <td><?php echo htmlspecialchars($treatment['treatment_type'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($treatment['medicine_name'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($treatment['dosage'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($treatment['prescribed_by_name'] ?: '-'); ?></td>
                        <td>
                            <a href="<?php echo APP_URL; ?>/modules/treatments/view.php?id=<?php echo $treatment['id']; ?>" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">💊</div>
            <h4>No Treatments Yet</h4>
            <p>No treatments have been recorded for this disease.</p>
            <a href="<?php echo APP_URL; ?>/modules/treatments/add.php?disease_id=<?php echo $id; ?>&animal_id=<?php echo $disease['animal_id']; ?>" class="btn btn-primary">Add Treatment</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
