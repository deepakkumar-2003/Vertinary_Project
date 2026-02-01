<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Add Disease Record';

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
    $diseaseName = sanitize($_POST['disease_name']);
    $symptoms = sanitize($_POST['symptoms']);
    $diagnosisDate = sanitize($_POST['diagnosis_date']);
    $severity = sanitize($_POST['severity']);
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);
    $diagnosedBy = getCurrentUserId();

    $stmt = $conn->prepare("INSERT INTO diseases (animal_id, disease_name, symptoms, diagnosed_by, diagnosis_date, severity, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississss", $animalId, $diseaseName, $symptoms, $diagnosedBy, $diagnosisDate, $severity, $status, $notes);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Added disease record: ' . $diseaseName, 'diseases');
        setMessage('Disease record added successfully', 'success');
        redirect(APP_URL . '/modules/diseases/list.php');
    } else {
        setMessage('Error adding disease record: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Add Disease Record</h3>
        <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="diseaseForm">
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
                    <label for="disease_name">Disease Name <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="disease_name" name="disease_name" class="form-control" required placeholder="Enter disease name">
                </div>
            </div>

            <div class="form-group">
                <label for="symptoms">Symptoms</label>
                <textarea id="symptoms" name="symptoms" class="form-control" rows="3" placeholder="Describe the symptoms"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="diagnosis_date">Diagnosis Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="diagnosis_date" name="diagnosis_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="severity">Severity <span style="color: var(--danger-color);">*</span></label>
                    <select id="severity" name="severity" class="form-control" required>
                        <option value="mild">Mild</option>
                        <option value="moderate" selected>Moderate</option>
                        <option value="severe">Severe</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" selected>Active</option>
                        <option value="recovered">Recovered</option>
                        <option value="chronic">Chronic</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Additional notes"></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Add Disease Record</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
