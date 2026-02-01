<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'View Insurance Policy';

// Get policy ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle claim addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_claim'])) {
    $claimDate = sanitize($_POST['claim_date']);
    $incidentDate = sanitize($_POST['incident_date']);
    $claimAmount = floatval($_POST['claim_amount']);
    $claimReason = sanitize($_POST['claim_reason']);
    $claimNotes = sanitize($_POST['claim_notes']);

    // Generate unique claim number
    $claimNumber = generateCode('CLM', 6);

    $stmt = $conn->prepare("INSERT INTO insurance_claims (policy_id, claim_number, claim_date, incident_date, claim_amount, claim_reason, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdss", $id, $claimNumber, $claimDate, $incidentDate, $claimAmount, $claimReason, $claimNotes);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Added claim ' . $claimNumber . ' for policy ID: ' . $id, 'insurance');
        setMessage('Claim submitted successfully', 'success');
    } else {
        setMessage('Error submitting claim: ' . $conn->error, 'error');
    }
    $stmt->close();
    redirect(APP_URL . '/modules/insurance/view.php?id=' . $id);
}

// Get policy details
$stmt = $conn->prepare("SELECT ip.*, c.name as customer_name, c.customer_code, c.id as customer_id, c.phone as customer_phone,
    a.animal_code, a.name as animal_name, a.id as animal_id
    FROM insurance_policies ip
    LEFT JOIN customers c ON ip.customer_id = c.id
    LEFT JOIN animals a ON ip.animal_id = a.id
    WHERE ip.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Insurance policy not found', 'error');
    redirect(APP_URL . '/modules/insurance/list.php');
}

$policy = $result->fetch_assoc();
$stmt->close();

// Get claims
$claimsQuery = $conn->query("SELECT * FROM insurance_claims WHERE policy_id = $id ORDER BY claim_date DESC");

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Insurance Policy Details</h3>
        <div class="btn-group">
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">Edit</a>
            <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
        </div>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Policy Number</div>
                <div class="detail-value"><strong><?php echo htmlspecialchars($policy['policy_number']); ?></strong></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Customer</div>
                <div class="detail-value">
                    <a href="<?php echo APP_URL; ?>/modules/customers/view.php?id=<?php echo $policy['customer_id']; ?>">
                        <?php echo htmlspecialchars($policy['customer_name']); ?>
                    </a>
                    <br><small><?php echo htmlspecialchars($policy['customer_code']); ?></small>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Customer Phone</div>
                <div class="detail-value"><?php echo htmlspecialchars($policy['customer_phone'] ?: '-'); ?></div>
            </div>

            <?php if ($policy['animal_id']): ?>
            <div class="detail-item">
                <div class="detail-label">Animal</div>
                <div class="detail-value">
                    <a href="<?php echo APP_URL; ?>/modules/animals/view.php?id=<?php echo $policy['animal_id']; ?>">
                        <?php echo htmlspecialchars($policy['animal_code']); ?>
                        <?php if ($policy['animal_name']): ?>
                        - <?php echo htmlspecialchars($policy['animal_name']); ?>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-item">
                <div class="detail-label">Insurance Company</div>
                <div class="detail-value"><?php echo htmlspecialchars($policy['insurance_company']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Policy Type</div>
                <div class="detail-value"><?php echo htmlspecialchars($policy['policy_type'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Coverage Amount</div>
                <div class="detail-value"><strong><?php echo formatCurrency($policy['coverage_amount']); ?></strong></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Premium Amount</div>
                <div class="detail-value"><?php echo $policy['premium_amount'] ? formatCurrency($policy['premium_amount']) : '-'; ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Start Date</div>
                <div class="detail-value"><?php echo formatDate($policy['start_date']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">End Date</div>
                <div class="detail-value">
                    <?php echo formatDate($policy['end_date']); ?>
                    <?php if ($policy['status'] === 'active' && strtotime($policy['end_date']) < time()): ?>
                    <br><span class="badge badge-danger">Expired</span>
                    <?php elseif ($policy['status'] === 'active'): ?>
                    <br><small><?php echo getDaysUntil($policy['end_date']); ?> days remaining</small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
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
                </div>
            </div>
        </div>

        <?php if ($policy['notes']): ?>
        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Notes</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($policy['notes'])); ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Claims History -->
<div class="card">
    <div class="card-header">
        <h3>Claims (<?php echo $claimsQuery->num_rows; ?>)</h3>
        <?php if ($policy['status'] === 'active'): ?>
        <button type="button" class="btn btn-primary btn-sm" onclick="showModal('addClaimModal')">+ File Claim</button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($claimsQuery->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Claim Number</th>
                        <th>Claim Date</th>
                        <th>Incident Date</th>
                        <th>Amount</th>
                        <th>Approved</th>
                        <th>Status</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($claim = $claimsQuery->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($claim['claim_number']); ?></strong></td>
                        <td><?php echo formatDate($claim['claim_date']); ?></td>
                        <td><?php echo formatDate($claim['incident_date']); ?></td>
                        <td><?php echo formatCurrency($claim['claim_amount']); ?></td>
                        <td><?php echo $claim['approved_amount'] ? formatCurrency($claim['approved_amount']) : '-'; ?></td>
                        <td>
                            <?php
                            $claimStatusClass = [
                                'submitted' => 'badge-warning',
                                'under_review' => 'badge-primary',
                                'approved' => 'badge-success',
                                'rejected' => 'badge-danger',
                                'paid' => 'badge-success'
                            ];
                            $class = $claimStatusClass[$claim['status']] ?? 'badge-secondary';
                            ?>
                            <span class="badge <?php echo $class; ?>"><?php echo ucfirst(str_replace('_', ' ', $claim['status'])); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars(substr($claim['claim_reason'], 0, 50) . (strlen($claim['claim_reason']) > 50 ? '...' : '')); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h4>No Claims Filed</h4>
            <p>No claims have been filed for this policy.</p>
            <?php if ($policy['status'] === 'active'): ?>
            <button type="button" class="btn btn-primary" onclick="showModal('addClaimModal')">File Claim</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Claim Modal -->
<?php if ($policy['status'] === 'active'): ?>
<div id="addClaimModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>File Insurance Claim</h3>
            <button type="button" class="modal-close" onclick="hideModal('addClaimModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="">
                <input type="hidden" name="add_claim" value="1">

                <div class="form-row two-cols">
                    <div class="form-group">
                        <label for="claim_date">Claim Date <span class="text-danger">*</span></label>
                        <input type="date" id="claim_date" name="claim_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="incident_date">Incident Date <span class="text-danger">*</span></label>
                        <input type="date" id="incident_date" name="incident_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="claim_amount">Claim Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" id="claim_amount" name="claim_amount" class="form-control" step="0.01" min="0.01" max="<?php echo $policy['coverage_amount']; ?>" required placeholder="Max: <?php echo number_format($policy['coverage_amount'], 2); ?>">
                </div>

                <div class="form-group">
                    <label for="claim_reason">Reason for Claim <span class="text-danger">*</span></label>
                    <textarea id="claim_reason" name="claim_reason" class="form-control" rows="3" required placeholder="Describe the incident and reason for claim"></textarea>
                </div>

                <div class="form-group">
                    <label for="claim_notes">Additional Notes</label>
                    <textarea id="claim_notes" name="claim_notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="hideModal('addClaimModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
