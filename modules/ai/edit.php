<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Edit AI Record';

// Get AI record ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get AI record details
$stmt = $conn->prepare("SELECT * FROM ai_records WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('AI record not found', 'error');
    redirect(APP_URL . '/modules/ai/list.php');
}

$ai = $result->fetch_assoc();
$stmt->close();

// Get female animals for dropdown
$animalsQuery = $conn->query("SELECT a.id, a.animal_code, a.name, a.species, c.name as customer_name
    FROM animals a
    LEFT JOIN customers c ON a.customer_id = c.id
    WHERE a.gender = 'female'
    ORDER BY a.animal_code");

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
    $actualDeliveryDate = !empty($_POST['actual_delivery_date']) ? sanitize($_POST['actual_delivery_date']) : null;
    $pregnancyStatus = sanitize($_POST['pregnancy_status']);
    $cost = !empty($_POST['cost']) ? floatval($_POST['cost']) : null;
    $notes = sanitize($_POST['notes']);

    $stmt = $conn->prepare("UPDATE ai_records SET animal_id = ?, ai_date = ?, bull_id = ?, bull_breed = ?, technician_name = ?, method = ?, first_checkup_date = ?, first_checkup_result = ?, second_checkup_date = ?, second_checkup_result = ?, expected_delivery_date = ?, actual_delivery_date = ?, pregnancy_status = ?, cost = ?, notes = ? WHERE id = ?");
    $stmt->bind_param("issssssssssssdsi", $animalId, $aiDate, $bullId, $bullBreed, $technicianName, $method, $firstCheckupDate, $firstCheckupResult, $secondCheckupDate, $secondCheckupResult, $expectedDeliveryDate, $actualDeliveryDate, $pregnancyStatus, $cost, $notes, $id);

    if ($stmt->execute()) {
        logActivity($conn, getCurrentUserId(), 'Updated AI record ID: ' . $id, 'ai');
        setMessage('AI record updated successfully', 'success');
        redirect(APP_URL . '/modules/ai/view.php?id=' . $id);
    } else {
        setMessage('Error updating AI record: ' . $conn->error, 'error');
    }

    $stmt->close();
}

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Edit AI Record</h3>
        <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="aiEditForm">
            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="animal_id">Animal (Female) <span style="color: var(--danger-color);">*</span></label>
                    <select id="animal_id" name="animal_id" class="form-control" required>
                        <option value="">Select Animal</option>
                        <?php while ($animal = $animalsQuery->fetch_assoc()): ?>
                        <option value="<?php echo $animal['id']; ?>" <?php echo $ai['animal_id'] == $animal['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($animal['animal_code'] . ' - ' . ($animal['name'] ?: ucfirst($animal['species'])) . ' (' . $animal['customer_name'] . ')'); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ai_date">AI Date <span style="color: var(--danger-color);">*</span></label>
                    <input type="date" id="ai_date" name="ai_date" class="form-control" required value="<?php echo $ai['ai_date']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bull_id">Bull ID / Semen Straw ID</label>
                    <input type="text" id="bull_id" name="bull_id" class="form-control" value="<?php echo htmlspecialchars($ai['bull_id']); ?>">
                </div>

                <div class="form-group">
                    <label for="bull_breed">Bull Breed</label>
                    <input type="text" id="bull_breed" name="bull_breed" class="form-control" value="<?php echo htmlspecialchars($ai['bull_breed']); ?>">
                </div>

                <div class="form-group">
                    <label for="technician_name">Technician Name</label>
                    <input type="text" id="technician_name" name="technician_name" class="form-control" value="<?php echo htmlspecialchars($ai['technician_name']); ?>">
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="method">Method <span style="color: var(--danger-color);">*</span></label>
                    <select id="method" name="method" class="form-control" required>
                        <option value="artificial" <?php echo $ai['method'] === 'artificial' ? 'selected' : ''; ?>>Artificial Insemination</option>
                        <option value="natural" <?php echo $ai['method'] === 'natural' ? 'selected' : ''; ?>>Natural Mating</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cost">Cost (₹)</label>
                    <input type="number" id="cost" name="cost" class="form-control" step="0.01" min="0" value="<?php echo $ai['cost']; ?>">
                </div>
            </div>

            <h4 style="margin: var(--spacing-lg) 0 var(--spacing-md); color: var(--primary-color);">Pregnancy Checkups</h4>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="first_checkup_date">First Checkup Date</label>
                    <input type="date" id="first_checkup_date" name="first_checkup_date" class="form-control" value="<?php echo $ai['first_checkup_date']; ?>">
                </div>

                <div class="form-group">
                    <label for="first_checkup_result">First Checkup Result</label>
                    <select id="first_checkup_result" name="first_checkup_result" class="form-control">
                        <option value="pending" <?php echo $ai['first_checkup_result'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="positive" <?php echo $ai['first_checkup_result'] === 'positive' ? 'selected' : ''; ?>>Positive</option>
                        <option value="negative" <?php echo $ai['first_checkup_result'] === 'negative' ? 'selected' : ''; ?>>Negative</option>
                    </select>
                </div>
            </div>

            <div class="form-row two-cols">
                <div class="form-group">
                    <label for="second_checkup_date">Second Checkup Date</label>
                    <input type="date" id="second_checkup_date" name="second_checkup_date" class="form-control" value="<?php echo $ai['second_checkup_date']; ?>">
                </div>

                <div class="form-group">
                    <label for="second_checkup_result">Second Checkup Result</label>
                    <select id="second_checkup_result" name="second_checkup_result" class="form-control">
                        <option value="pending" <?php echo $ai['second_checkup_result'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="positive" <?php echo $ai['second_checkup_result'] === 'positive' ? 'selected' : ''; ?>>Positive</option>
                        <option value="negative" <?php echo $ai['second_checkup_result'] === 'negative' ? 'selected' : ''; ?>>Negative</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pregnancy_status">Pregnancy Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="pregnancy_status" name="pregnancy_status" class="form-control" required>
                        <option value="not_confirmed" <?php echo $ai['pregnancy_status'] === 'not_confirmed' ? 'selected' : ''; ?>>Not Confirmed</option>
                        <option value="confirmed" <?php echo $ai['pregnancy_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="failed" <?php echo $ai['pregnancy_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="delivered" <?php echo $ai['pregnancy_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="expected_delivery_date">Expected Delivery Date</label>
                    <input type="date" id="expected_delivery_date" name="expected_delivery_date" class="form-control" value="<?php echo $ai['expected_delivery_date']; ?>">
                </div>

                <div class="form-group">
                    <label for="actual_delivery_date">Actual Delivery Date</label>
                    <input type="date" id="actual_delivery_date" name="actual_delivery_date" class="form-control" value="<?php echo $ai['actual_delivery_date']; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($ai['notes']); ?></textarea>
            </div>

            <div class="btn-group mt-20">
                <button type="submit" class="btn btn-primary">Update AI Record</button>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
