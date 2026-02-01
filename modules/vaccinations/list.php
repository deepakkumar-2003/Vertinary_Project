<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Vaccinations';

// TODO: Implement vaccination list functionality
// Reference: modules/customers/list.php for pattern

include_once INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Vaccinations Management</h3>
        <a href="add.php" class="btn btn-primary btn-sm">+ Add Vaccination</a>
    </div>
    <div class="card-body">
        <p class="text-center text-muted">Vaccination module to be implemented. Follow the same pattern as Customer and Animal modules.</p>
        <p class="text-center">
            <strong>Key Features to Implement:</strong><br>
            - List all vaccinations with search and filter<br>
            - Show upcoming vaccinations (next 30 days)<br>
            - Highlight overdue vaccinations<br>
            - Auto-generate reminders<br>
            - Track vaccination status (scheduled, completed, overdue, skipped)
        </p>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
