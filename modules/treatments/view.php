<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'View Treatment';

// Get treatment ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get treatment details
$stmt = $conn->prepare("SELECT t.*, a.animal_code, a.name as animal_name, a.species, a.id as animal_id,
    c.name as customer_name, c.id as customer_id, u.full_name as prescribed_by_name,
    d.disease_name, d.id as disease_id
    FROM treatments t
    LEFT JOIN animals a ON t.animal_id = a.id
    LEFT JOIN customers c ON a.customer_id = c.id
    LEFT JOIN users u ON t.prescribed_by = u.id
    LEFT JOIN diseases d ON t.disease_id = d.id
    WHERE t.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Treatment record not found', 'error');
    redirect(APP_URL . '/modules/treatments/list.php');
}

$treatment = $result->fetch_assoc();
$stmt->close();

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Treatment Record Details</h3>
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
                    <a href="<?php echo APP_URL; ?>/modules/animals/view.php?id=<?php echo $treatment['animal_id']; ?>">
                        <strong><?php echo htmlspecialchars($treatment['animal_code']); ?></strong>
                        <?php if ($treatment['animal_name']): ?>
                        - <?php echo htmlspecialchars($treatment['animal_name']); ?>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Species</div>
                <div class="detail-value"><?php echo ucfirst($treatment['species']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Customer</div>
                <div class="detail-value">
                    <a href="<?php echo APP_URL; ?>/modules/customers/view.php?id=<?php echo $treatment['customer_id']; ?>">
                        <?php echo htmlspecialchars($treatment['customer_name']); ?>
                    </a>
                </div>
            </div>

            <?php if ($treatment['disease_name']): ?>
            <div class="detail-item">
                <div class="detail-label">Related Disease</div>
                <div class="detail-value">
                    <a href="<?php echo APP_URL; ?>/modules/diseases/view.php?id=<?php echo $treatment['disease_id']; ?>">
                        <?php echo htmlspecialchars($treatment['disease_name']); ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-item">
                <div class="detail-label">Treatment Date</div>
                <div class="detail-value"><?php echo formatDate($treatment['treatment_date']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Prescribed By</div>
                <div class="detail-value"><?php echo htmlspecialchars($treatment['prescribed_by_name'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Treatment Type</div>
                <div class="detail-value"><?php echo htmlspecialchars($treatment['treatment_type'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Medicine Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($treatment['medicine_name'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Dosage</div>
                <div class="detail-value"><?php echo htmlspecialchars($treatment['dosage'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Frequency</div>
                <div class="detail-value"><?php echo htmlspecialchars($treatment['frequency'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Duration</div>
                <div class="detail-value"><?php echo htmlspecialchars($treatment['duration'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Route</div>
                <div class="detail-value"><?php echo htmlspecialchars($treatment['route'] ?: '-'); ?></div>
            </div>

            <?php if ($treatment['start_date']): ?>
            <div class="detail-item">
                <div class="detail-label">Start Date</div>
                <div class="detail-value"><?php echo formatDate($treatment['start_date']); ?></div>
            </div>
            <?php endif; ?>

            <?php if ($treatment['end_date']): ?>
            <div class="detail-item">
                <div class="detail-label">End Date</div>
                <div class="detail-value"><?php echo formatDate($treatment['end_date']); ?></div>
            </div>
            <?php endif; ?>

            <div class="detail-item">
                <div class="detail-label">Cost</div>
                <div class="detail-value"><?php echo $treatment['cost'] ? formatCurrency($treatment['cost']) : '-'; ?></div>
            </div>
        </div>

        <?php if ($treatment['instructions']): ?>
        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Instructions</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($treatment['instructions'])); ?></div>
        </div>
        <?php endif; ?>

        <?php if ($treatment['notes']): ?>
        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Notes</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($treatment['notes'])); ?></div>
        </div>
        <?php endif; ?>

        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Created At</div>
            <div class="detail-value"><?php echo formatDateTime($treatment['created_at']); ?></div>
        </div>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
