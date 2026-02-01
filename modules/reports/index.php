<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Reports';

// TODO: Implement reporting functionality

include_once INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Reports & Analytics</h3>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <span>🩺</span>
                </div>
                <div class="stat-info">
                    <h4>Health Reports</h4>
                    <p class="text-muted">Disease, treatment, and vaccination reports</p>
                    <a href="#" class="btn btn-sm btn-primary">Generate Report</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon success">
                    <span>🥛</span>
                </div>
                <div class="stat-info">
                    <h4>Dairy Production Reports</h4>
                    <p class="text-muted">Daily, weekly, monthly milk production</p>
                    <a href="#" class="btn btn-sm btn-primary">Generate Report</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon warning">
                    <span>💰</span>
                </div>
                <div class="stat-info">
                    <h4>Financial Reports</h4>
                    <p class="text-muted">Loans, insurance, and payments</p>
                    <a href="#" class="btn btn-sm btn-primary">Generate Report</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon danger">
                    <span>💉</span>
                </div>
                <div class="stat-info">
                    <h4>Vaccination Schedule</h4>
                    <p class="text-muted">Upcoming and overdue vaccinations</p>
                    <a href="#" class="btn btn-sm btn-primary">Generate Report</a>
                </div>
            </div>
        </div>

        <p class="text-center text-muted mt-20">
            <strong>Reports module to be implemented.</strong><br>
            Implement report generation with PDF export using libraries like TCPDF or FPDF.<br>
            For Excel export, use PHPSpreadsheet library.
        </p>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
