<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'View AI Record';

// Get AI record ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get AI record details
$stmt = $conn->prepare("SELECT ai.*, a.animal_code, a.name as animal_name, a.species, a.id as animal_id,
    c.name as customer_name, c.id as customer_id, u.full_name as performed_by_name
    FROM ai_records ai
    LEFT JOIN animals a ON ai.animal_id = a.id
    LEFT JOIN customers c ON a.customer_id = c.id
    LEFT JOIN users u ON ai.performed_by = u.id
    WHERE ai.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('AI record not found', 'error');
    redirect(APP_URL . '/modules/ai/list.php');
}

$ai = $result->fetch_assoc();
$stmt->close();

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>AI Record Details</h3>
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
                    <a href="<?php echo APP_URL; ?>/modules/animals/view.php?id=<?php echo $ai['animal_id']; ?>">
                        <strong><?php echo htmlspecialchars($ai['animal_code']); ?></strong>
                        <?php if ($ai['animal_name']): ?>
                        - <?php echo htmlspecialchars($ai['animal_name']); ?>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Species</div>
                <div class="detail-value"><?php echo ucfirst($ai['species']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Customer</div>
                <div class="detail-value">
                    <a href="<?php echo APP_URL; ?>/modules/customers/view.php?id=<?php echo $ai['customer_id']; ?>">
                        <?php echo htmlspecialchars($ai['customer_name']); ?>
                    </a>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">AI Date</div>
                <div class="detail-value"><?php echo formatDate($ai['ai_date']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Method</div>
                <div class="detail-value"><?php echo $ai['method'] === 'artificial' ? 'Artificial Insemination' : 'Natural Mating'; ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Bull ID / Straw ID</div>
                <div class="detail-value"><?php echo htmlspecialchars($ai['bull_id'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Bull Breed</div>
                <div class="detail-value"><?php echo htmlspecialchars($ai['bull_breed'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Technician</div>
                <div class="detail-value"><?php echo htmlspecialchars($ai['technician_name'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Performed By</div>
                <div class="detail-value"><?php echo htmlspecialchars($ai['performed_by_name'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Cost</div>
                <div class="detail-value"><?php echo $ai['cost'] ? formatCurrency($ai['cost']) : '-'; ?></div>
            </div>
        </div>

        <h4 style="margin: var(--spacing-lg) 0 var(--spacing-md); color: var(--primary-color);">Pregnancy Status</h4>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Pregnancy Status</div>
                <div class="detail-value">
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
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">First Checkup Date</div>
                <div class="detail-value"><?php echo $ai['first_checkup_date'] ? formatDate($ai['first_checkup_date']) : '-'; ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">First Checkup Result</div>
                <div class="detail-value">
                    <?php
                    $resultClass = ['pending' => 'badge-warning', 'positive' => 'badge-success', 'negative' => 'badge-danger'];
                    $class = $resultClass[$ai['first_checkup_result']] ?? 'badge-secondary';
                    ?>
                    <span class="badge <?php echo $class; ?>"><?php echo ucfirst($ai['first_checkup_result'] ?: 'pending'); ?></span>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Second Checkup Date</div>
                <div class="detail-value"><?php echo $ai['second_checkup_date'] ? formatDate($ai['second_checkup_date']) : '-'; ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Second Checkup Result</div>
                <div class="detail-value">
                    <?php
                    $class = $resultClass[$ai['second_checkup_result']] ?? 'badge-secondary';
                    ?>
                    <span class="badge <?php echo $class; ?>"><?php echo ucfirst($ai['second_checkup_result'] ?: 'pending'); ?></span>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Expected Delivery Date</div>
                <div class="detail-value"><?php echo $ai['expected_delivery_date'] ? formatDate($ai['expected_delivery_date']) : '-'; ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Actual Delivery Date</div>
                <div class="detail-value"><?php echo $ai['actual_delivery_date'] ? formatDate($ai['actual_delivery_date']) : '-'; ?></div>
            </div>
        </div>

        <?php if ($ai['notes']): ?>
        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Notes</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($ai['notes'])); ?></div>
        </div>
        <?php endif; ?>

        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Created At</div>
            <div class="detail-value"><?php echo formatDateTime($ai['created_at']); ?></div>
        </div>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
