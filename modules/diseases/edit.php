<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Edit Disease Record';

// Get disease ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get disease details
$stmt = $conn->prepare("SELECT d.*, a.animal_code, a.name as animal_name
    FROM diseases d
    LEFT JOIN animals a ON d.animal_id = a.id
    WHERE d.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Disease record not found', 'error');
    redirect(APP_URL . '/modules/diseases/list.php');
}

$disease = $result->fetch_assoc();
$stmt->close();

// Get animals for dropdown
$animalsQuery = $conn->query("SELECT a.id, a.animal_code, a.name, a.species, c.name as customer_name
    FROM animals a
    LEFT JOIN customers c ON a.customer_id = c.id
    ORDER BY a.animal_code");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animalId = intval($_POST['animal_id']);
    $diseaseName = sanitize($_POST['disease_name']);
    $symptoms = sanitize($_POST['symptoms']);
    $diagnosisDate = sanitize($_POST['diagnosis_date']);
    $severity = sanitize($_POST['severity']);
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);

    $stmt = $conn->prepare("UPDATE diseases SET animal_id = ?, disease_name = ?, symptoms = ?, diagnosis_date = ?, severity = ?, status = ?, notes = ? WHERE id = ?");
    $stmt->bind_param("issssssi", $animalId, $diseaseName, $symptoms, $diagnosisDate, $severity, $status, $notes, $id);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Updated disease record: ' . $diseaseName, 'diseases');
        setMessage('Disease record updated successfully', 'success');
        redirect(APP_URL . '/modules/diseases/view.php?id=' . $id);
    } else {
        setMessage('Error updating disease record: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Edit Disease Record</h3>
        <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="diseaseEditForm">
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="animal_id">Animal <span style="color: var(--danger-color);">*</span></label>
                    <select id="animal_id" name="animal_id" class="form-control" required>
                        <option value="">Select Animal</option>
                        <?php while ($animal = $animalsQuery->fetch_assoc()): ?>
                        <option value="<?php echo $animal['id']; ?>" <?php echo $disease['animal_id'] == $animal['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($animal['animal_code'] . ' - ' . ($animal['name'] ?: ucfirst($animal['species'])) . ' (' . $animal['customer_name'] . ')'); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="disease_name">Disease Name <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="disease_name" name="disease_name" class="form-control" required value="<?php echo htmlspecialchars($disease['disease_name']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="symptoms">Symptoms</label>
                <textarea id="symptoms" name="symptoms" class="form-control" rows="3"><?php echo htmlspecialchars($disease['symptoms']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="diagnosis_date">Diagnosis Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="diagnosis_date" name="diagnosis_date" class="form-control" required value="<?php echo $disease['diagnosis_date']; ?>">
                </div>

                <div class="form-group">
                    <label for="severity">Severity <span style="color: var(--danger-color);">*</span></label>
                    <select id="severity" name="severity" class="form-control" required>
                        <option value="mild" <?php echo $disease['severity'] === 'mild' ? 'selected' : ''; ?>>Mild</option>
                        <option value="moderate" <?php echo $disease['severity'] === 'moderate' ? 'selected' : ''; ?>>Moderate</option>
                        <option value="severe" <?php echo $disease['severity'] === 'severe' ? 'selected' : ''; ?>>Severe</option>
                        <option value="critical" <?php echo $disease['severity'] === 'critical' ? 'selected' : ''; ?>>Critical</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?php echo $disease['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="recovered" <?php echo $disease['status'] === 'recovered' ? 'selected' : ''; ?>>Recovered</option>
                        <option value="chronic" <?php echo $disease['status'] === 'chronic' ? 'selected' : ''; ?>>Chronic</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($disease['notes']); ?></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Update Disease Record</button>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
