<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Add Vaccination';

// Get animals for dropdown
$animalsQuery = $conn->query("SELECT a.id, a.animal_code, a.name, a.species, c.name as customer_name
    FROM animals a
    LEFT JOIN customers c ON a.customer_id = c.id
    WHERE a.status = 'active'
    ORDER BY a.animal_code");

// Pre-select animal if passed via URL
$selectedAnimalId = isset($_GET['animal_id']) ? intval($_GET['animal_id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animalId = intval($_POST['animal_id']);
    $vaccineName = sanitize($_POST['vaccine_name']);
    $vaccineType = sanitize($_POST['vaccine_type']);
    $batchNumber = sanitize($_POST['batch_number']);
    $administeredDate = sanitize($_POST['administered_date']);
    $nextDueDate = !empty($_POST['next_due_date']) ? sanitize($_POST['next_due_date']) : null;
    $status = sanitize($_POST['status']);
    $cost = !empty($_POST['cost']) ? floatval($_POST['cost']) : null;
    $notes = sanitize($_POST['notes']);
    $administeredBy = getCurrentUserId();

    $stmt = $conn->prepare("INSERT INTO vaccinations (animal_id, vaccine_name, vaccine_type, batch_number, administered_by, administered_date, next_due_date, status, cost, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississssds", $animalId, $vaccineName, $vaccineType, $batchNumber, $administeredBy, $administeredDate, $nextDueDate, $status, $cost, $notes);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Added vaccination record: ' . $vaccineName, 'vaccinations');
        setMessage('Vaccination record added successfully', 'success');
        redirect(APP_URL . '/modules/vaccinations/list.php');
    } else {
        setMessage('Error adding vaccination record: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add Vaccination Record</h3>
        <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="vaccinationForm">
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="animal_id">Animal <span style="color: var(--danger-color);">*</span></label>
                    <select id="animal_id" name="animal_id" class="form-control" required>
                        <option value="">Select Animal</option>
                        <?php while ($animal = $animalsQuery->fetch_assoc()): ?>
                        <option value="<?php echo $animal['id']; ?>" <?php echo $selectedAnimalId == $animal['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($animal['animal_code'] . ' - ' . ($animal['name'] ?: ucfirst($animal['species'])) . ' (' . $animal['customer_name'] . ')'); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="vaccine_name">Vaccine Name <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="vaccine_name" name="vaccine_name" class="form-control" required placeholder="Enter vaccine name">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="vaccine_type">Vaccine Type</label>
                    <input type="text" id="vaccine_type" name="vaccine_type" class="form-control" placeholder="e.g., FMD, Brucellosis, Anthrax">
                </div>

                <div class="form-group">
                    <label for="batch_number">Batch Number</label>
                    <input type="text" id="batch_number" name="batch_number" class="form-control" placeholder="Enter batch number">
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="completed" selected>Completed</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="overdue">Overdue</option>
                        <option value="skipped">Skipped</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="administered_date">Administered Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="administered_date" name="administered_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="next_due_date">Next Due Date</label>
                    <input type="date" id="next_due_date" name="next_due_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="cost">Cost (₹)</label>
                    <input type="number" id="cost" name="cost" class="form-control" step="0.01" min="0" placeholder="Enter cost">
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Additional notes"></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Add Vaccination</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
