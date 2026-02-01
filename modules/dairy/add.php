<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Add Dairy Record';

// Get animals for dropdown (only cattle and buffalo typically produce milk)
$animalsQuery = $conn->query("SELECT a.id, a.animal_code, a.name, a.species, c.name as customer_name
    FROM animals a
    LEFT JOIN customers c ON a.customer_id = c.id
    WHERE a.status = 'active' AND a.gender = 'female' AND a.species IN ('cattle', 'buffalo', 'goat', 'sheep')
    ORDER BY a.animal_code");

// Pre-select animal if passed via URL
$selectedAnimalId = isset($_GET['animal_id']) ? intval($_GET['animal_id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animalId = intval($_POST['animal_id']);
    $recordDate = sanitize($_POST['record_date']);
    $morningMilk = !empty($_POST['morning_milk']) ? floatval($_POST['morning_milk']) : 0;
    $afternoonMilk = !empty($_POST['afternoon_milk']) ? floatval($_POST['afternoon_milk']) : 0;
    $eveningMilk = !empty($_POST['evening_milk']) ? floatval($_POST['evening_milk']) : 0;
    $fatPercentage = !empty($_POST['fat_percentage']) ? floatval($_POST['fat_percentage']) : null;
    $snfPercentage = !empty($_POST['snf_percentage']) ? floatval($_POST['snf_percentage']) : null;
    $qualityGrade = sanitize($_POST['quality_grade']);
    $notes = sanitize($_POST['notes']);
    $recordedBy = getCurrentUserId();

    // Check if record already exists for this animal on this date
    $checkStmt = $conn->prepare("SELECT id FROM dairy_records WHERE animal_id = ? AND record_date = ?");
    $checkStmt->bind_param("is", $animalId, $recordDate);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        setMessage('A dairy record already exists for this animal on the selected date. Please edit the existing record.', 'error');
    } else {
        $stmt = $conn->prepare("INSERT INTO dairy_records (animal_id, record_date, morning_milk, afternoon_milk, evening_milk, fat_percentage, snf_percentage, quality_grade, recorded_by, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddddsis", $animalId, $recordDate, $morningMilk, $afternoonMilk, $eveningMilk, $fatPercentage, $snfPercentage, $qualityGrade, $recordedBy, $notes);

        if ($stmt->execute()) {
            logActivity($conn, getCurrentUserId(), 'Added dairy record for animal ID: ' . $animalId, 'dairy');
            setMessage('Dairy record added successfully', 'success');
            redirect(APP_URL . '/modules/dairy/list.php');
        } else {
            setMessage('Error adding dairy record: ' . $conn->error, 'error');
        }

        $stmt->close();
    }
    $checkStmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add Dairy Record</h3>
        <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="dairyForm">
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
                    <label for="record_date">Record Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="record_date" name="record_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="morning_milk">Morning Milk (Liters)</label>
                    <input type="number" id="morning_milk" name="morning_milk" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="afternoon_milk">Afternoon Milk (Liters)</label>
                    <input type="number" id="afternoon_milk" name="afternoon_milk" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="evening_milk">Evening Milk (Liters)</label>
                    <input type="number" id="evening_milk" name="evening_milk" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fat_percentage">Fat Percentage (%)</label>
                    <input type="number" id="fat_percentage" name="fat_percentage" class="form-control" step="0.01" min="0" max="100" placeholder="e.g., 4.5">
                </div>

                <div class="form-group">
                    <label for="snf_percentage">SNF Percentage (%)</label>
                    <input type="number" id="snf_percentage" name="snf_percentage" class="form-control" step="0.01" min="0" max="100" placeholder="e.g., 8.5">
                </div>

                <div class="form-group">
                    <label for="quality_grade">Quality Grade</label>
                    <select id="quality_grade" name="quality_grade" class="form-control">
                        <option value="">Select Grade</option>
                        <option value="A">Grade A (Excellent)</option>
                        <option value="B">Grade B (Good)</option>
                        <option value="C">Grade C (Average)</option>
                        <option value="D">Grade D (Below Average)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Additional notes"></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Add Dairy Record</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
