<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Add Loan';

// Get customers for dropdown
$customersQuery = $conn->query("SELECT id, customer_code, name FROM customers WHERE status = 'active' ORDER BY name");

// Pre-select customer if passed via URL
$selectedCustomerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

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

    // Generate unique loan number
    $loanNumber = generateCode('LOAN', 6);

    // Check if loan number exists
    $stmt = $conn->prepare("SELECT id FROM loans WHERE loan_number = ?");
    $stmt->bind_param("s", $loanNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $loanNumber = generateCode('LOAN', 8);
    }

    // Calculate remaining amount (initially equals loan amount)
    $remainingAmount = $loanAmount;

    $stmt = $conn->prepare("INSERT INTO loans (customer_id, loan_number, loan_type, loan_amount, interest_rate, loan_date, due_date, status, paid_amount, remaining_amount, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)");
    $stmt->bind_param("issddsssds", $customerId, $loanNumber, $loanType, $loanAmount, $interestRate, $loanDate, $dueDate, $status, $remainingAmount, $notes);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Added loan: ' . $loanNumber, 'loans');
        setMessage('Loan added successfully', 'success');
        redirect(APP_URL . '/modules/loans/list.php');
    } else {
        setMessage('Error adding loan: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add New Loan</h3>
        <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="loanForm">
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="customer_id">Customer <span style="color: var(--danger-color);">*</span></label>
                    <select id="customer_id" name="customer_id" class="form-control" required>
                        <option value="">Select Customer</option>
                        <?php while ($customer = $customersQuery->fetch_assoc()): ?>
                        <option value="<?php echo $customer['id']; ?>" <?php echo $selectedCustomerId == $customer['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['customer_code'] . ' - ' . $customer['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="loan_type">Loan Type</label>
                    <select id="loan_type" name="loan_type" class="form-control">
                        <option value="">Select Type</option>
                        <option value="Animal Purchase">Animal Purchase</option>
                        <option value="Equipment">Equipment</option>
                        <option value="Feed & Supplies">Feed & Supplies</option>
                        <option value="Medical Emergency">Medical Emergency</option>
                        <option value="Farm Improvement">Farm Improvement</option>
                        <option value="Working Capital">Working Capital</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="loan_amount">Loan Amount (₹) <span style="color: var(--danger-color);">*</span></label>
                    <input type="number" id="loan_amount" name="loan_amount" class="form-control" step="0.01" min="0" required placeholder="Enter loan amount">
                </div>

                <div class="form-group">
                    <label for="interest_rate">Interest Rate (%)</label>
                    <input type="number" id="interest_rate" name="interest_rate" class="form-control" step="0.01" min="0" max="100" placeholder="Enter interest rate">
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" selected>Active</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                        <option value="defaulted">Defaulted</option>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="loan_date">Loan Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="loan_date" name="loan_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="due_date" name="due_date" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Additional notes about the loan"></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Add Loan</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
