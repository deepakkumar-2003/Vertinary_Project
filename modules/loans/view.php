<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'View Loan';

// Get loan ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle payment addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment'])) {
    $paymentDate = sanitize($_POST['payment_date']);
    $paymentAmount = floatval($_POST['payment_amount']);
    $paymentMethod = sanitize($_POST['payment_method']);
    $paymentNotes = sanitize($_POST['payment_notes']);
    $receivedBy = getCurrentUserId();

    $stmt = $conn->prepare("INSERT INTO loan_payments (loan_id, payment_date, payment_amount, payment_method, received_by, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdsss", $id, $paymentDate, $paymentAmount, $paymentMethod, $receivedBy, $paymentNotes);

    if ($stmt->execute()) {
        // Update loan paid and remaining amounts
        $conn->query("UPDATE loans SET paid_amount = paid_amount + $paymentAmount, remaining_amount = remaining_amount - $paymentAmount WHERE id = $id");

        // Check if loan is fully paid
        $loanCheck = $conn->query("SELECT remaining_amount FROM loans WHERE id = $id")->fetch_assoc();
        if ($loanCheck['remaining_amount'] <= 0) {
            $conn->query("UPDATE loans SET status = 'paid', remaining_amount = 0 WHERE id = $id");
        }

        logActivity($conn, getCurrentUserId(), 'Added payment of ' . formatCurrency($paymentAmount) . ' to loan ID: ' . $id, 'loans');
        setMessage('Payment recorded successfully', 'success');
    } else {
        setMessage('Error recording payment: ' . $conn->error, 'error');
    }
    $stmt->close();
    redirect(APP_URL . '/modules/loans/view.php?id=' . $id);
}

// Get loan details
$stmt = $conn->prepare("SELECT l.*, c.name as customer_name, c.customer_code, c.id as customer_id, c.phone as customer_phone
    FROM loans l
    LEFT JOIN customers c ON l.customer_id = c.id
    WHERE l.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Loan not found', 'error');
    redirect(APP_URL . '/modules/loans/list.php');
}

$loan = $result->fetch_assoc();
$stmt->close();

// Get loan payments
$paymentsQuery = $conn->query("SELECT p.*, u.full_name as received_by_name
    FROM loan_payments p
    LEFT JOIN users u ON p.received_by = u.id
    WHERE p.loan_id = $id
    ORDER BY p.payment_date DESC");

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Loan Details</h3>
        <div class="btn-group">
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">Edit</a>
            <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
        </div>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Loan Number</div>
                <div class="detail-value"><strong><?php echo htmlspecialchars($loan['loan_number']); ?></strong></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Customer</div>
                <div class="detail-value">
                    <a href="<?php echo APP_URL; ?>/modules/customers/view.php?id=<?php echo $loan['customer_id']; ?>">
                        <?php echo htmlspecialchars($loan['customer_name']); ?>
                    </a>
                    <br><small><?php echo htmlspecialchars($loan['customer_code']); ?></small>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Customer Phone</div>
                <div class="detail-value"><?php echo htmlspecialchars($loan['customer_phone'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Loan Type</div>
                <div class="detail-value"><?php echo htmlspecialchars($loan['loan_type'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Loan Amount</div>
                <div class="detail-value"><strong><?php echo formatCurrency($loan['loan_amount']); ?></strong></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Interest Rate</div>
                <div class="detail-value"><?php echo $loan['interest_rate'] ? $loan['interest_rate'] . '%' : '-'; ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Loan Date</div>
                <div class="detail-value"><?php echo formatDate($loan['loan_date']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Due Date</div>
                <div class="detail-value">
                    <?php echo formatDate($loan['due_date']); ?>
                    <?php if ($loan['status'] !== 'paid' && strtotime($loan['due_date']) < time()): ?>
                    <br><span class="badge badge-danger">Overdue</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
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
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div style="background: var(--light-color); padding: var(--spacing-lg); border-radius: var(--border-radius); margin-top: var(--spacing-lg);">
            <h4 style="margin-bottom: var(--spacing-md);">Payment Summary</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Total Amount</div>
                    <div class="detail-value"><strong><?php echo formatCurrency($loan['loan_amount']); ?></strong></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Paid Amount</div>
                    <div class="detail-value" style="color: var(--success-color);"><strong><?php echo formatCurrency($loan['paid_amount']); ?></strong></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Remaining Amount</div>
                    <div class="detail-value" style="color: var(--danger-color);"><strong><?php echo formatCurrency($loan['remaining_amount']); ?></strong></div>
                </div>
            </div>
        </div>

        <?php if ($loan['notes']): ?>
        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Notes</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($loan['notes'])); ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment History -->
<div class="card">
    <div class="card-header">
        <h3>Payment History (<?php echo $paymentsQuery->num_rows; ?>)</h3>
        <?php if ($loan['status'] !== 'paid'): ?>
        <button type="button" class="btn btn-primary btn-sm" onclick="showModal('addPaymentModal')">+ Add Payment</button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($paymentsQuery->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Received By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payment = $paymentsQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo formatDate($payment['payment_date']); ?></td>
                        <td><strong><?php echo formatCurrency($payment['payment_amount']); ?></strong></td>
                        <td><?php echo htmlspecialchars($payment['payment_method'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($payment['received_by_name'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($payment['notes'] ?: '-'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">💵</div>
            <h4>No Payments Yet</h4>
            <p>No payments have been recorded for this loan.</p>
            <?php if ($loan['status'] !== 'paid'): ?>
            <button type="button" class="btn btn-primary" onclick="showModal('addPaymentModal')">Add Payment</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Payment Modal -->
<?php if ($loan['status'] !== 'paid'): ?>
<div id="addPaymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Payment</h3>
            <button type="button" class="modal-close" onclick="hideModal('addPaymentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="">
                <input type="hidden" name="add_payment" value="1">

                <div class="form-group">
                    <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" id="payment_date" name="payment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="payment_amount">Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" id="payment_amount" name="payment_amount" class="form-control" step="0.01" min="0.01" max="<?php echo $loan['remaining_amount']; ?>" required placeholder="Max: <?php echo number_format($loan['remaining_amount'], 2); ?>">
                </div>

                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-control">
                        <option value="">Select Method</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="UPI">UPI</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="payment_notes">Notes</label>
                    <textarea id="payment_notes" name="payment_notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="hideModal('addPaymentModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
