<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Edit Customer';

// Get customer ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get customer details
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Customer not found', 'error');
    redirect(APP_URL . '/modules/customers/list.php');
}

$customer = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $pincode = sanitize($_POST['pincode']);
    $emergencyContactName = sanitize($_POST['emergency_contact_name']);
    $emergencyContactPhone = sanitize($_POST['emergency_contact_phone']);
    $status = sanitize($_POST['status']);

    // Update customer
    $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, email = ?, address = ?, city = ?, state = ?, pincode = ?, emergency_contact_name = ?, emergency_contact_phone = ?, status = ? WHERE id = ?");

    $stmt->bind_param("ssssssssssi", $name, $phone, $email, $address, $city, $state, $pincode, $emergencyContactName, $emergencyContactPhone, $status, $id);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Updated customer: ' . $name, 'customers');
        setMessage('Customer updated successfully', 'success');
        redirect(APP_URL . '/modules/customers/view.php?id=' . $id);
    } else {
        setMessage('Error updating customer: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Edit Customer</h3>
        <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="customerEditForm">
            <!-- Customer Code (Read-only) -->
            <div class="form-group">
                <label>Customer Code</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($customer['customer_code']); ?>" disabled>
            </div>

            <!-- Basic Information -->
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="name">Full Name <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number <span style="color: var(--danger-color);">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email']); ?>">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control"><?php echo htmlspecialchars($customer['address']); ?></textarea>
            </div>

            <!-- Location Information -->
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($customer['city']); ?>">
                </div>

                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" class="form-control" value="<?php echo htmlspecialchars($customer['state']); ?>">
                </div>

                <div class="form-group">
                    <label for="pincode">Pincode</label>
                    <input type="text" id="pincode" name="pincode" class="form-control" value="<?php echo htmlspecialchars($customer['pincode']); ?>" pattern="[0-9]{6}" maxlength="6">
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" value="<?php echo htmlspecialchars($customer['emergency_contact_name']); ?>">
                </div>

                <div class="form-group">
                    <label for="emergency_contact_phone">Emergency Contact Phone</label>
                    <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" value="<?php echo htmlspecialchars($customer['emergency_contact_phone']); ?>">
                </div>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    <option value="active" <?php echo $customer['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $customer['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <!-- Form Actions -->
            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Update Customer</button>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
