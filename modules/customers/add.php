<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Add Customer';

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

    // Generate unique customer code
    $customerCode = generateCode('CUS', 6);

    // Check if code already exists
    $stmt = $conn->prepare("SELECT id FROM customers WHERE customer_code = ?");
    $stmt->bind_param("s", $customerCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customerCode = generateCode('CUS', 8); // Generate longer code if duplicate
    }

    // Insert customer
    $stmt = $conn->prepare("INSERT INTO customers (customer_code, name, phone, email, address, city, state, pincode, emergency_contact_name, emergency_contact_phone, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $createdBy = getCurrentUserId();
    $stmt->bind_param("sssssssssssi", $customerCode, $name, $phone, $email, $address, $city, $state, $pincode, $emergencyContactName, $emergencyContactPhone, $status, $createdBy);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Added new customer: ' . $name, 'customers');
        setMessage('Customer added successfully', 'success');
        redirect(APP_URL . '/modules/customers/list.php');
    } else {
        setMessage('Error adding customer: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add New Customer</h3>
        <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="customerForm">
            <!-- Basic Information -->
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="name">Full Name <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required placeholder="Enter full name">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number <span style="color: var(--danger-color);">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control" required placeholder="Enter 10-digit phone number">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" placeholder="Enter complete address"></textarea>
            </div>

            <!-- Location Information -->
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" placeholder="Enter city">
                </div>

                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" class="form-control" placeholder="Enter state">
                </div>

                <div class="form-group">
                    <label for="pincode">Pincode</label>
                    <input type="text" id="pincode" name="pincode" class="form-control" pattern="[0-9]{6}" maxlength="6" placeholder="Enter 6-digit pincode">
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" placeholder="Enter emergency contact name">
                </div>

                <div class="form-group">
                    <label for="emergency_contact_phone">Emergency Contact Phone</label>
                    <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" placeholder="Enter emergency contact phone">
                </div>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- Form Actions -->
            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Add Customer</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
