<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Edit Insurance Policy';

// Get policy ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get policy details
$stmt = $conn->prepare("SELECT * FROM insurance_policies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Insurance policy not found', 'error');
    redirect(APP_URL . '/modules/insurance/list.php');
}

$policy = $result->fetch_assoc();
$stmt->close();

// Get customers for dropdown
$customersQuery = $conn->query("SELECT id, customer_code, name FROM customers ORDER BY name");

// Get animals for dropdown
$animalsQuery = $conn->query("SELECT a.id, a.animal_code, a.name, a.species, c.name as customer_name, c.id as customer_id
    FROM animals a
    LEFT JOIN customers c ON a.customer_id = c.id
    ORDER BY a.animal_code");

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

    $stmt = $conn->prepare("UPDATE insurance_policies SET customer_id = ?, animal_id = ?, insurance_company = ?, policy_type = ?, coverage_amount = ?, premium_amount = ?, start_date = ?, end_date = ?, status = ?, notes = ? WHERE id = ?");
    $stmt->bind_param("iissddssss", $customerId, $animalId, $insuranceCompany, $policyType, $coverageAmount, $premiumAmount, $startDate, $endDate, $status, $notes, $id);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Updated insurance policy: ' . $policy['policy_number'], 'insurance');
        setMessage('Insurance policy updated successfully', 'success');
        redirect(APP_URL . '/modules/insurance/view.php?id=' . $id);
    } else {
        setMessage('Error updating policy: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Edit Insurance Policy</h3>
        <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="insuranceEditForm">
            <!-- Policy Number (Read-only) -->
            <div class="form-group">
                <label>Policy Number</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($policy['policy_number']); ?>" disabled>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="customer_id">Customer <span style="color: var(--danger-color);">*</span></label>
                    <select id="customer_id" name="customer_id" class="form-control" required>
                        <option value="">Select Customer</option>
                        <?php while ($customer = $customersQuery->fetch_assoc()): ?>
                        <option value="<?php echo $customer['id']; ?>" <?php echo $policy['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $animal['id']; ?>" data-customer="<?php echo $animal['customer_id']; ?>" <?php echo $policy['animal_id'] == $animal['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($animal['animal_code'] . ' - ' . ($animal['name'] ?: ucfirst($animal['species'])) . ' (' . $animal['customer_name'] . ')'); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="insurance_company">Insurance Company <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="insurance_company" name="insurance_company" class="form-control" required value="<?php echo htmlspecialchars($policy['insurance_company']); ?>">
                </div>

                <div class="form-group">
                    <label for="policy_type">Policy Type</label>
                    <select id="policy_type" name="policy_type" class="form-control">
                        <option value="">Select Type</option>
                        <option value="Livestock Insurance" <?php echo $policy['policy_type'] === 'Livestock Insurance' ? 'selected' : ''; ?>>Livestock Insurance</option>
                        <option value="Cattle Insurance" <?php echo $policy['policy_type'] === 'Cattle Insurance' ? 'selected' : ''; ?>>Cattle Insurance</option>
                        <option value="Dairy Animal Insurance" <?php echo $policy['policy_type'] === 'Dairy Animal Insurance' ? 'selected' : ''; ?>>Dairy Animal Insurance</option>
                        <option value="Poultry Insurance" <?php echo $policy['policy_type'] === 'Poultry Insurance' ? 'selected' : ''; ?>>Poultry Insurance</option>
                        <option value="Comprehensive Farm Insurance" <?php echo $policy['policy_type'] === 'Comprehensive Farm Insurance' ? 'selected' : ''; ?>>Comprehensive Farm Insurance</option>
                        <option value="Other" <?php echo $policy['policy_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="coverage_amount">Coverage Amount (₹) <span style="color: var(--danger-color);">*</span></label>
                    <input type="number" id="coverage_amount" name="coverage_amount" class="form-control" step="0.01" min="0" required value="<?php echo $policy['coverage_amount']; ?>">
                </div>

                <div class="form-group">
                    <label for="premium_amount">Premium Amount (₹)</label>
                    <input type="number" id="premium_amount" name="premium_amount" class="form-control" step="0.01" min="0" value="<?php echo $policy['premium_amount']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required value="<?php echo $policy['start_date']; ?>">
                </div>

                <div class="form-group">
                    <label for="end_date">End Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required value="<?php echo $policy['end_date']; ?>">
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?php echo $policy['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="expired" <?php echo $policy['status'] === 'expired' ? 'selected' : ''; ?>>Expired</option>
                        <option value="cancelled" <?php echo $policy['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="claimed" <?php echo $policy['status'] === 'claimed' ? 'selected' : ''; ?>>Claimed</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($policy['notes']); ?></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Update Policy</button>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
