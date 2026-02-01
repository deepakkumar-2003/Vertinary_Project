<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'View Animal';

// Get animal ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get animal details with customer info
$stmt = $conn->prepare("SELECT a.*, c.name as customer_name, c.customer_code, c.phone as customer_phone
    FROM animals a
    JOIN customers c ON a.customer_id = c.id
    WHERE a.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Animal not found', 'error');
    redirect(APP_URL . '/modules/animals/list.php');
}

$animal = $result->fetch_assoc();
$stmt->close();

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Animal Details</h3>
        <div class="d-flex gap-10">
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">Edit</a>
            <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
        </div>
    </div>
    <div class="card-body">
        <table style="width: 100%;">
            <tr>
                <td style="padding: 10px; font-weight: 600; width: 200px;">Animal Code:</td>
                <td style="padding: 10px;"><strong><?php echo htmlspecialchars($animal['animal_code']); ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Name:</td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($animal['name'] ?: '-'); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Customer:</td>
                <td style="padding: 10px;">
                    <a href="<?php echo APP_URL; ?>/modules/customers/view.php?id=<?php echo $animal['customer_id']; ?>">
                        <?php echo htmlspecialchars($animal['customer_name']); ?> (<?php echo htmlspecialchars($animal['customer_code']); ?>)
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Species:</td>
                <td style="padding: 10px;"><?php echo ucfirst($animal['species']); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Breed:</td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($animal['breed'] ?: '-'); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Gender:</td>
                <td style="padding: 10px;"><?php echo ucfirst($animal['gender']); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Date of Birth:</td>
                <td style="padding: 10px;"><?php echo $animal['date_of_birth'] ? formatDate($animal['date_of_birth']) : '-'; ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Age:</td>
                <td style="padding: 10px;">
                    <?php
                    if ($animal['date_of_birth']) {
                        echo calculateAge($animal['date_of_birth']);
                    } else if ($animal['age_years'] || $animal['age_months']) {
                        echo ($animal['age_years'] ? $animal['age_years'] . ' year(s) ' : '') .
                             ($animal['age_months'] ? $animal['age_months'] . ' month(s)' : '');
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Color:</td>
                <td style="padding: 10px;"><?php echo htmlspecialchars($animal['color'] ?: '-'); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Identification Marks:</td>
                <td style="padding: 10px;"><?php echo nl2br(htmlspecialchars($animal['identification_marks'] ?: '-')); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Weight:</td>
                <td style="padding: 10px;"><?php echo $animal['weight'] ? $animal['weight'] . ' kg' : '-'; ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Status:</td>
                <td style="padding: 10px;"><?php echo getStatusBadge($animal['status']); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: 600;">Registered On:</td>
                <td style="padding: 10px;"><?php echo formatDateTime($animal['created_at']); ?></td>
            </tr>
        </table>
    </div>
</div>

<!-- Quick Actions -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <span>🩺</span>
        </div>
        <div class="stat-info">
            <h4>Diseases</h4>
            <a href="<?php echo APP_URL; ?>/modules/diseases/list.php?animal_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">View Records</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <span>💊</span>
        </div>
        <div class="stat-info">
            <h4>Treatments</h4>
            <a href="<?php echo APP_URL; ?>/modules/treatments/list.php?animal_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">View Records</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <span>💉</span>
        </div>
        <div class="stat-info">
            <h4>Vaccinations</h4>
            <a href="<?php echo APP_URL; ?>/modules/vaccinations/list.php?animal_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">View Records</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">
            <span>🔬</span>
        </div>
        <div class="stat-info">
            <h4>AI Records</h4>
            <a href="<?php echo APP_URL; ?>/modules/ai/list.php?animal_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">View Records</a>
        </div>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
