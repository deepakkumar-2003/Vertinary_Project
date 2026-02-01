<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Add Treatment';

// Get animals for dropdown
$animalsQuery = $conn->query("SELECT a.id, a.animal_code, a.name, a.species, c.name as customer_name
    FROM animals a
    LEFT JOIN customers c ON a.customer_id = c.id
    WHERE a.status = 'active'
    ORDER BY a.animal_code");

// Pre-select animal and disease if passed via URL
$selectedAnimalId = isset($_GET['animal_id']) ? intval($_GET['animal_id']) : 0;
$selectedDiseaseId = isset($_GET['disease_id']) ? intval($_GET['disease_id']) : 0;

// Get diseases for dropdown (will be filtered by JS if animal is selected)
$diseasesQuery = $conn->query("SELECT d.id, d.disease_name, d.animal_id, a.animal_code
    FROM diseases d
    LEFT JOIN animals a ON d.animal_id = a.id
    WHERE d.status = 'active'
    ORDER BY d.diagnosis_date DESC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animalId = intval($_POST['animal_id']);
    $diseaseId = !empty($_POST['disease_id']) ? intval($_POST['disease_id']) : null;
    $treatmentDate = sanitize($_POST['treatment_date']);
    $treatmentType = sanitize($_POST['treatment_type']);
    $medicineName = sanitize($_POST['medicine_name']);
    $dosage = sanitize($_POST['dosage']);
    $frequency = sanitize($_POST['frequency']);
    $duration = sanitize($_POST['duration']);
    $route = sanitize($_POST['route']);
    $instructions = sanitize($_POST['instructions']);
    $startDate = !empty($_POST['start_date']) ? sanitize($_POST['start_date']) : null;
    $endDate = !empty($_POST['end_date']) ? sanitize($_POST['end_date']) : null;
    $cost = !empty($_POST['cost']) ? floatval($_POST['cost']) : null;
    $notes = sanitize($_POST['notes']);
    $prescribedBy = getCurrentUserId();

    $stmt = $conn->prepare("INSERT INTO treatments (animal_id, disease_id, treatment_date, prescribed_by, treatment_type, medicine_name, dosage, frequency, duration, route, instructions, start_date, end_date, cost, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisisssssssssds", $animalId, $diseaseId, $treatmentDate, $prescribedBy, $treatmentType, $medicineName, $dosage, $frequency, $duration, $route, $instructions, $startDate, $endDate, $cost, $notes);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Added treatment record', 'treatments');
        setMessage('Treatment record added successfully', 'success');
        redirect(APP_URL . '/modules/treatments/list.php');
    } else {
        setMessage('Error adding treatment record: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add Treatment Record</h3>
        <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="treatmentForm">
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
                    <label for="disease_id">Related Disease</label>
                    <select id="disease_id" name="disease_id" class="form-control">
                        <option value="">No Disease / General Treatment</option>
                        <?php while ($disease = $diseasesQuery->fetch_assoc()): ?>
                        <option value="<?php echo $disease['id']; ?>" data-animal="<?php echo $disease['animal_id']; ?>" <?php echo $selectedDiseaseId == $disease['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($disease['animal_code'] . ' - ' . $disease['disease_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="treatment_date">Treatment Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="treatment_date" name="treatment_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="treatment_type">Treatment Type</label>
                    <input type="text" id="treatment_type" name="treatment_type" class="form-control" placeholder="e.g., Medication, Surgery, Therapy">
                </div>

                <div class="form-group">
                    <label for="medicine_name">Medicine Name</label>
                    <input type="text" id="medicine_name" name="medicine_name" class="form-control" placeholder="Enter medicine name">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dosage">Dosage</label>
                    <input type="text" id="dosage" name="dosage" class="form-control" placeholder="e.g., 500mg, 10ml">
                </div>

                <div class="form-group">
                    <label for="frequency">Frequency</label>
                    <input type="text" id="frequency" name="frequency" class="form-control" placeholder="e.g., Twice daily, Every 8 hours">
                </div>

                <div class="form-group">
                    <label for="duration">Duration</label>
                    <input type="text" id="duration" name="duration" class="form-control" placeholder="e.g., 7 days, 2 weeks">
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="route">Route of Administration</label>
                    <select id="route" name="route" class="form-control">
                        <option value="">Select Route</option>
                        <option value="Oral">Oral</option>
                        <option value="Injection">Injection</option>
                        <option value="Topical">Topical</option>
                        <option value="Intravenous">Intravenous (IV)</option>
                        <option value="Intramuscular">Intramuscular (IM)</option>
                        <option value="Subcutaneous">Subcutaneous (SC)</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cost">Cost (₹)</label>
                    <input type="number" id="cost" name="cost" class="form-control" step="0.01" min="0" placeholder="Enter cost">
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control">
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="instructions">Instructions</label>
                <textarea id="instructions" name="instructions" class="form-control" rows="3" placeholder="Special instructions for treatment"></textarea>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Additional notes"></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Add Treatment</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
