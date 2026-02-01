<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Add Insurance Policy';

// Get customers for dropdown
$customersQuery = $conn->query("SELECT id, customer_code, name FROM customers WHERE status = 'active' ORDER BY name");

// Get animals for dropdown
$animalsQuery = $conn->query("SELECT a.id, a.animal_code, a.name, a.species, c.name as customer_name, c.id as customer_id
    FROM animals a
    LEFT JOIN customers c ON a.customer_id = c.id
    WHERE a.status = 'active'
    ORDER BY a.animal_code");

// Pre-select if passed via URL
$selectedCustomerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$selectedAnimalId = isset($_GET['animal_id']) ? intval($_GET['animal_id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = intval($_POST['customer_id']);
    $animalId = !empty($_POST['animal_id']) ? intval($_POST['animal_id']) : null;
    $insuranceCompany = sanitize($_POST['insurance_company']);
    $policyType = sanitize($_POST['policy_type']);
    $coverageAmount = floatval($_POST['coverage_amount']);
    $premiumAmount = !empty($_POST['premium_amount']) ? floatval($_POST['premium_amount']) : null;
    $startDate = sanitize($_POST['start_date']);
    $endDate = sanitize($_POST['end_date']);
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);

    // Generate unique policy number
    $policyNumber = generateCode('POL', 6);

    // Check if policy number exists
    $stmt = $conn->prepare("SELECT id FROM insurance_policies WHERE policy_number = ?");
    $stmt->bind_param("s", $policyNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $policyNumber = generateCode('POL', 8);
    }

    $stmt = $conn->prepare("INSERT INTO insurance_policies (customer_id, animal_id, policy_number, insurance_company, policy_type, coverage_amount, premium_amount, start_date, end_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssddsss", $customerId, $animalId, $policyNumber, $insuranceCompany, $policyType, $coverageAmount, $premiumAmount, $startDate, $endDate, $status, $notes);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Added insurance policy: ' . $policyNumber, 'insurance');
        setMessage('Insurance policy added successfully', 'success');
        redirect(APP_URL . '/modules/insurance/list.php');
    } else {
        setMessage('Error adding policy: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add Insurance Policy</h3>
        <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="insuranceForm">
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
                    <label for="animal_id">Animal (Optional)</label>
                    <select id="animal_id" name="animal_id" class="form-control">
                        <option value="">Select Animal (for animal-specific policy)</option>
                        <?php while ($animal = $animalsQuery->fetch_assoc()): ?>
                        <option value="<?php echo $animal['id']; ?>" data-customer="<?php echo $animal['customer_id']; ?>" <?php echo $selectedAnimalId == $animal['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($animal['animal_code'] . ' - ' . ($animal['name'] ?: ucfirst($animal['species'])) . ' (' . $animal['customer_name'] . ')'); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="insurance_company">Insurance Company <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="insurance_company" name="insurance_company" class="form-control" required placeholder="Enter insurance company name">
                </div>

                <div class="form-group">
                    <label for="policy_type">Policy Type</label>
                    <select id="policy_type" name="policy_type" class="form-control">
                        <option value="">Select Type</option>
                        <option value="Livestock Insurance">Livestock Insurance</option>
                        <option value="Cattle Insurance">Cattle Insurance</option>
                        <option value="Dairy Animal Insurance">Dairy Animal Insurance</option>
                        <option value="Poultry Insurance">Poultry Insurance</option>
                        <option value="Comprehensive Farm Insurance">Comprehensive Farm Insurance</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="coverage_amount">Coverage Amount (₹) <span style="color: var(--danger-color);">*</span></label>
                    <input type="number" id="coverage_amount" name="coverage_amount" class="form-control" step="0.01" min="0" required placeholder="Enter coverage amount">
                </div>

                <div class="form-group">
                    <label for="premium_amount">Premium Amount (₹)</label>
                    <input type="number" id="premium_amount" name="premium_amount" class="form-control" step="0.01" min="0" placeholder="Enter premium amount">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="end_date">End Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" selected>Active</option>
                        <option value="expired">Expired</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="claimed">Claimed</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Additional notes about the policy"></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Add Policy</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
