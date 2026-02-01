<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'View Customer';

// Get customer ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get customer details with creator info
$stmt = $conn->prepare("SELECT c.*, u.full_name as created_by_name
    FROM customers c
    LEFT JOIN users u ON c.created_by = u.id
    WHERE c.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setMessage('Customer not found', 'error');
    redirect(APP_URL . '/modules/customers/list.php');
}

$customer = $result->fetch_assoc();
$stmt->close();

// Get customer's animals
$animalsQuery = $conn->query("SELECT * FROM animals WHERE customer_id = $id ORDER BY created_at DESC");

include_once INCLUDES_PATH . '/header.php';
?>

<?php displayMessage(); ?>

<div class="card">
    <div class="card-header">
        <h3>Customer Details</h3>
        <div class="btn-group">
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">Edit</a>
            <a href="list.php" class="btn btn-outline btn-sm">← Back to List</a>
        </div>
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Customer Code</div>
                <div class="detail-value"><strong><?php echo htmlspecialchars($customer['customer_code']); ?></strong></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Full Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($customer['name']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Phone Number</div>
                <div class="detail-value"><?php echo htmlspecialchars($customer['phone']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Email Address</div>
                <div class="detail-value"><?php echo htmlspecialchars($customer['email'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">City</div>
                <div class="detail-value"><?php echo htmlspecialchars($customer['city'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">State</div>
                <div class="detail-value"><?php echo htmlspecialchars($customer['state'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Pincode</div>
                <div class="detail-value"><?php echo htmlspecialchars($customer['pincode'] ?: '-'); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value"><?php echo getStatusBadge($customer['status']); ?></div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Created By</div>
                <div class="detail-value"><?php echo htmlspecialchars($customer['created_by_name'] ?: '-'); ?></div>
            </div>
        </div>

        <?php if ($customer['address']): ?>
        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Address</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></div>
        </div>
        <?php endif; ?>

        <?php if ($customer['emergency_contact_name'] || $customer['emergency_contact_phone']): ?>
        <div class="detail-grid mt-20">
            <div class="detail-item">
                <div class="detail-label">Emergency Contact Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($customer['emergency_contact_name'] ?: '-'); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Emergency Contact Phone</div>
                <div class="detail-value"><?php echo htmlspecialchars($customer['emergency_contact_phone'] ?: '-'); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="detail-item mt-20" style="background: var(--light-color); padding: var(--spacing-md); border-radius: var(--border-radius);">
            <div class="detail-label">Created At</div>
            <div class="detail-value"><?php echo formatDateTime($customer['created_at']); ?></div>
        </div>
    </div>
</div>

<!-- Customer's Animals -->
<div class="card">
    <div class="card-header">
        <h3>Animals (<?php echo $animalsQuery->num_rows; ?>)</h3>
        <a href="<?php echo APP_URL; ?>/modules/animals/add.php?customer_id=<?php echo $id; ?>" class="btn btn-primary btn-sm">+ Add Animal</a>
    </div>
    <div class="card-body">
        <?php if ($animalsQuery->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Animal Code</th>
                        <th>Name</th>
                        <th>Species</th>
                        <th>Breed</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($animal = $animalsQuery->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($animal['animal_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($animal['name'] ?: '-'); ?></td>
                        <td><?php echo ucfirst($animal['species']); ?></td>
                        <td><?php echo htmlspecialchars($animal['breed'] ?: '-'); ?></td>
                        <td><?php echo ucfirst($animal['gender']); ?></td>
                        <td>
                            <?php
                            if ($animal['date_of_birth']) {
                                echo calculateAge($animal['date_of_birth']);
                            } else {
                                echo $animal['age_years'] ? $animal['age_years'] . ' year(s)' : '-';
                            }
                            ?>
                        </td>
                        <td><?php echo getStatusBadge($animal['status']); ?></td>
                        <td>
                            <a href="<?php echo APP_URL; ?>/modules/animals/view.php?id=<?php echo $animal['id']; ?>" class="btn btn-sm btn-outline">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">🐄</div>
            <h4>No Animals Registered</h4>
            <p>This customer doesn't have any animals registered yet.</p>
            <a href="<?php echo APP_URL; ?>/modules/animals/add.php?customer_id=<?php echo $id; ?>" class="btn btn-primary">Add First Animal</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
