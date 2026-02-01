<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Add AI Record';

// Get female animals for dropdown (only female animals for AI)
$animalsQuery = $conn->query("SELECT a.id, a.animal_code, a.name, a.species, c.name as customer_name
    FROM animals a
    LEFT JOIN customers c ON a.customer_id = c.id
    WHERE a.status = 'active' AND a.gender = 'female'
    ORDER BY a.animal_code");

// Pre-select animal if passed via URL
$selectedAnimalId = isset($_GET['animal_id']) ? intval($_GET['animal_id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animalId = intval($_POST['animal_id']);
    $aiDate = sanitize($_POST['ai_date']);
    $bullId = sanitize($_POST['bull_id']);
    $bullBreed = sanitize($_POST['bull_breed']);
    $technicianName = sanitize($_POST['technician_name']);
    $method = sanitize($_POST['method']);
    $firstCheckupDate = !empty($_POST['first_checkup_date']) ? sanitize($_POST['first_checkup_date']) : null;
    $firstCheckupResult = sanitize($_POST['first_checkup_result']);
    $secondCheckupDate = !empty($_POST['second_checkup_date']) ? sanitize($_POST['second_checkup_date']) : null;
    $secondCheckupResult = sanitize($_POST['second_checkup_result']);
    $expectedDeliveryDate = !empty($_POST['expected_delivery_date']) ? sanitize($_POST['expected_delivery_date']) : null;
    $pregnancyStatus = sanitize($_POST['pregnancy_status']);
    $cost = !empty($_POST['cost']) ? floatval($_POST['cost']) : null;
    $notes = sanitize($_POST['notes']);
    $performedBy = getCurrentUserId();

    $stmt = $conn->prepare("INSERT INTO ai_records (animal_id, ai_date, bull_id, bull_breed, technician_name, performed_by, method, first_checkup_date, first_checkup_result, second_checkup_date, second_checkup_result, expected_delivery_date, pregnancy_status, cost, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssississssds", $animalId, $aiDate, $bullId, $bullBreed, $technicianName, $performedBy, $method, $firstCheckupDate, $firstCheckupResult, $secondCheckupDate, $secondCheckupResult, $expectedDeliveryDate, $pregnancyStatus, $cost, $notes);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Added AI record for animal ID: ' . $animalId, 'ai');
        setMessage('AI record added successfully', 'success');
        redirect(APP_URL . '/modules/ai/list.php');
    } else {
        setMessage('Error adding AI record: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add AI Record</h3>
        <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="aiForm">
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="animal_id">Animal (Female) <span style="color: var(--danger-color);">*</span></label>
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
                    <label for="ai_date">AI Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="ai_date" name="ai_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bull_id">Bull ID / Semen Straw ID</label>
                    <input type="text" id="bull_id" name="bull_id" class="form-control" placeholder="Enter bull/straw ID">
                </div>

                <div class="form-group">
                    <label for="bull_breed">Bull Breed</label>
                    <input type="text" id="bull_breed" name="bull_breed" class="form-control" placeholder="e.g., Holstein, Jersey">
                </div>

                <div class="form-group">
                    <label for="technician_name">Technician Name</label>
                    <input type="text" id="technician_name" name="technician_name" class="form-control" placeholder="Enter technician name">
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="method">Method <span style="color: var(--danger-color);">*</span></label>
                    <select id="method" name="method" class="form-control" required>
                        <option value="artificial" selected>Artificial Insemination</option>
                        <option value="natural">Natural Mating</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cost">Cost (₹)</label>
                    <input type="number" id="cost" name="cost" class="form-control" step="0.01" min="0" placeholder="Enter cost">
                </div>
            </div>

            <h4 style="margin: var(--spacing-lg) 0 var(--spacing-md); color: var(--primary-color);">Pregnancy Checkups</h4>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="first_checkup_date">First Checkup Date</label>
                    <input type="date" id="first_checkup_date" name="first_checkup_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="first_checkup_result">First Checkup Result</label>
                    <select id="first_checkup_result" name="first_checkup_result" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="positive">Positive</option>
                        <option value="negative">Negative</option>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="second_checkup_date">Second Checkup Date</label>
                    <input type="date" id="second_checkup_date" name="second_checkup_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="second_checkup_result">Second Checkup Result</label>
                    <select id="second_checkup_result" name="second_checkup_result" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="positive">Positive</option>
                        <option value="negative">Negative</option>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="pregnancy_status">Pregnancy Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="pregnancy_status" name="pregnancy_status" class="form-control" required>
                        <option value="not_confirmed" selected>Not Confirmed</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="failed">Failed</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="expected_delivery_date">Expected Delivery Date</label>
                    <input type="date" id="expected_delivery_date" name="expected_delivery_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Additional notes"></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Add AI Record</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
