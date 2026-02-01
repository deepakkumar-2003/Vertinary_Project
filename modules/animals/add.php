<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Add Animal';

// Get customer_id if passed
$preselected_customer = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

// Get all active customers for dropdown
$customersQuery = $conn->query("SELECT id, customer_code, name FROM customers WHERE status = 'active' ORDER BY name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $name = sanitize($_POST['name']);
    $species = sanitize($_POST['species']);
    $breed = sanitize($_POST['breed']);
    $gender = sanitize($_POST['gender']);
    $date_of_birth = !empty($_POST['date_of_birth']) ? sanitize($_POST['date_of_birth']) : null;
    $age_years = !empty($_POST['age_years']) ? intval($_POST['age_years']) : null;
    $age_months = !empty($_POST['age_months']) ? intval($_POST['age_months']) : null;
    $color = sanitize($_POST['color']);
    $identification_marks = sanitize($_POST['identification_marks']);
    $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : null;
    $status = sanitize($_POST['status']);

    // Generate unique animal code
    $animalCode = generateCode('ANI', 6);

    // Check if code already exists
    $stmt = $conn->prepare("SELECT id FROM animals WHERE animal_code = ?");
    $stmt->bind_param("s", $animalCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $animalCode = generateCode('ANI', 8);
    }

    // Insert animal
    $stmt = $conn->prepare("INSERT INTO animals (animal_code, customer_id, name, species, breed, gender, date_of_birth, age_years, age_months, color, identification_marks, weight, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sisssssiisdss", $animalCode, $customer_id, $name, $species, $breed, $gender, $date_of_birth, $age_years, $age_months, $color, $identification_marks, $weight, $status);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Added new animal: ' . $animalCode, 'animals');
        setMessage('Animal added successfully', 'success');
        redirect(APP_URL . '/modules/animals/list.php');
    } else {
        setMessage('Error adding animal: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add New Animal</h3>
        <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="animalForm">
            <!-- Customer Selection -->
            <div class="form-group">
                <label for="customer_id">Customer <span style="color: var(--danger-color);">*</span></label>
                <select id="customer_id" name="customer_id" class="form-control" required>
                    <option value="">Select Customer</option>
                    <?php while ($customer = $customersQuery->fetch_assoc()): ?>
                        <option value="<?php echo $customer['id']; ?>" <?php echo ($preselected_customer == $customer['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['customer_code'] . ' - ' . $customer['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Basic Information -->
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="name">Animal Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter animal name (optional)">
                </div>

                <div class="form-group">
                    <label for="species">Species <span style="color: var(--danger-color);">*</span></label>
                    <select id="species" name="species" class="form-control" required>
                        <option value="">Select Species</option>
                        <option value="cattle">Cattle</option>
                        <option value="buffalo">Buffalo</option>
                        <option value="goat">Goat</option>
                        <option value="sheep">Sheep</option>
                        <option value="pig">Pig</option>
                        <option value="poultry">Poultry</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="breed">Breed</label>
                    <input type="text" id="breed" name="breed" class="form-control" placeholder="Enter breed">
                </div>

                <div class="form-group">
                    <label for="gender">Gender <span style="color: var(--danger-color);">*</span></label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>

            <!-- Age Information -->
            <div class="form-row">
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                    <small class="text-muted">If unknown, enter age below</small>
                </div>

                <div class="form-group">
                    <label for="age_years">Age (Years)</label>
                    <input type="number" id="age_years" name="age_years" class="form-control" min="0" placeholder="Years">
                </div>

                <div class="form-group">
                    <label for="age_months">Age (Months)</label>
                    <input type="number" id="age_months" name="age_months" class="form-control" min="0" max="11" placeholder="Months">
                </div>
            </div>

            <!-- Physical Attributes -->
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" class="form-control" placeholder="Enter color">
                </div>

                <div class="form-group">
                    <label for="weight">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" class="form-control" step="0.01" min="0" placeholder="Enter weight in kg">
                </div>
            </div>

            <div class="form-group">
                <label for="identification_marks">Identification Marks</label>
                <textarea id="identification_marks" name="identification_marks" class="form-control" placeholder="Describe any identification marks"></textarea>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    <option value="active">Active</option>
                    <option value="sold">Sold</option>
                    <option value="deceased">Deceased</option>
                </select>
            </div>

            <!-- Form Actions -->
            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Add Animal</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
