<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Edit Loan';

// Get loan ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get loan details
$stmt = $conn->prepare("SELECT * FROM loans WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Loan not found', 'error');
    redirect(APP_URL . '/modules/loans/list.php');
}

$loan = $result->fetch_assoc();
$stmt->close();

// Get customers for dropdown
$customersQuery = $conn->query("SELECT id, customer_code, name FROM customers ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = intval($_POST['customer_id']);
    $loanType = sanitize($_POST['loan_type']);
    $loanAmount = floatval($_POST['loan_amount']);
    $interestRate = !empty($_POST['interest_rate']) ? floatval($_POST['interest_rate']) : null;
    $loanDate = sanitize($_POST['loan_date']);
    $dueDate = sanitize($_POST['due_date']);
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);

    // Recalculate remaining amount
    $paidAmount = $loan['paid_amount'];
    $remainingAmount = $loanAmount - $paidAmount;
    if ($remainingAmount < 0) $remainingAmount = 0;

    $stmt = $conn->prepare("UPDATE loans SET customer_id = ?, loan_type = ?, loan_amount = ?, interest_rate = ?, loan_date = ?, due_date = ?, status = ?, remaining_amount = ?, notes = ? WHERE id = ?");
    $stmt->bind_param("isddsssdsi", $customerId, $loanType, $loanAmount, $interestRate, $loanDate, $dueDate, $status, $remainingAmount, $notes, $id);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Updated loan: ' . $loan['loan_number'], 'loans');
        setMessage('Loan updated successfully', 'success');
        redirect(APP_URL . '/modules/loans/view.php?id=' . $id);
    } else {
        setMessage('Error updating loan: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Edit Loan</h3>
        <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="loanEditForm">
            <!-- Loan Number (Read-only) -->
            <div class="form-group">
                <label>Loan Number</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($loan['loan_number']); ?>" disabled>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="customer_id">Customer <span style="color: var(--danger-color);">*</span></label>
                    <select id="customer_id" name="customer_id" class="form-control" required>
                        <option value="">Select Customer</option>
                        <?php while ($customer = $customersQuery->fetch_assoc()): ?>
                        <option value="<?php echo $customer['id']; ?>" <?php echo $loan['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['customer_code'] . ' - ' . $customer['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="loan_type">Loan Type</label>
                    <select id="loan_type" name="loan_type" class="form-control">
                        <option value="">Select Type</option>
                        <option value="Animal Purchase" <?php echo $loan['loan_type'] === 'Animal Purchase' ? 'selected' : ''; ?>>Animal Purchase</option>
                        <option value="Equipment" <?php echo $loan['loan_type'] === 'Equipment' ? 'selected' : ''; ?>>Equipment</option>
                        <option value="Feed & Supplies" <?php echo $loan['loan_type'] === 'Feed & Supplies' ? 'selected' : ''; ?>>Feed & Supplies</option>
                        <option value="Medical Emergency" <?php echo $loan['loan_type'] === 'Medical Emergency' ? 'selected' : ''; ?>>Medical Emergency</option>
                        <option value="Farm Improvement" <?php echo $loan['loan_type'] === 'Farm Improvement' ? 'selected' : ''; ?>>Farm Improvement</option>
                        <option value="Working Capital" <?php echo $loan['loan_type'] === 'Working Capital' ? 'selected' : ''; ?>>Working Capital</option>
                        <option value="Other" <?php echo $loan['loan_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="loan_amount">Loan Amount (₹) <span style="color: var(--danger-color);">*</span></label>
                    <input type="number" id="loan_amount" name="loan_amount" class="form-control" step="0.01" min="0" required value="<?php echo $loan['loan_amount']; ?>">
                </div>

                <div class="form-group">
                    <label for="interest_rate">Interest Rate (%)</label>
                    <input type="number" id="interest_rate" name="interest_rate" class="form-control" step="0.01" min="0" max="100" value="<?php echo $loan['interest_rate']; ?>">
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?php echo $loan['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="paid" <?php echo $loan['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="overdue" <?php echo $loan['status'] === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                        <option value="defaulted" <?php echo $loan['status'] === 'defaulted' ? 'selected' : ''; ?>>Defaulted</option>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="loan_date">Loan Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="loan_date" name="loan_date" class="form-control" required value="<?php echo $loan['loan_date']; ?>">
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="due_date" name="due_date" class="form-control" required value="<?php echo $loan['due_date']; ?>">
                </div>
            </div>

            <!-- Payment Info (Read-only) -->
            <div class="form-row two-cols">
                <div class="form-group">
                    <label>Paid Amount</label>
                    <input type="text" class="form-control" value="<?php echo formatCurrency($loan['paid_amount']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Remaining Amount</label>
                    <input type="text" class="form-control" value="<?php echo formatCurrency($loan['remaining_amount']); ?>" disabled>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($loan['notes']); ?></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Update Loan</button>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
