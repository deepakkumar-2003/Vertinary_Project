<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#2563eb">
    <meta name="description" content="Veterinary Management System - Comprehensive animal healthcare management">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/css/style.css">
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div class="dashboard">
        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><?php echo APP_NAME; ?></h2>
                <p>Version <?php echo APP_VERSION; ?></p>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="<?php echo APP_URL; ?>/public/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <span>📊</span> Dashboard
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/customers/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'customers') !== false ? 'active' : ''; ?>">
                        <span>👥</span> Customers
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/animals/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'animals') !== false ? 'active' : ''; ?>">
                        <span>🐄</span> Animals
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/diseases/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'diseases') !== false ? 'active' : ''; ?>">
                        <span>🩺</span> Diseases
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/treatments/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'treatments') !== false ? 'active' : ''; ?>">
                        <span>💊</span> Treatments
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/vaccinations/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'vaccinations') !== false ? 'active' : ''; ?>">
                        <span>💉</span> Vaccinations
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/ai/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/ai/') !== false ? 'active' : ''; ?>">
                        <span>🔬</span> AI Records
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/dairy/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'dairy') !== false ? 'active' : ''; ?>">
                        <span>🥛</span> Milk Dairy
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/loans/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'loans') !== false ? 'active' : ''; ?>">
                        <span>💰</span> Loans
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/insurance/list.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'insurance') !== false ? 'active' : ''; ?>">
                        <span>🛡️</span> Insurance
                    </a>
                </li>

                <li>
                    <a href="<?php echo APP_URL; ?>/modules/reports/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'active' : ''; ?>">
                        <span>📈</span> Reports
                    </a>
                </li>

                <?php if (hasRole('admin')): ?>
                <li>
                    <a href="<?php echo APP_URL; ?>/modules/auth/users.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
                        <span>⚙️</span> User Management
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="main-content">
            <!-- Top Navigation -->
            <nav class="topbar">
                <div class="topbar-left">
                    <!-- Mobile Menu Toggle -->
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h3><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h3>
                </div>

                <div class="topbar-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr(getCurrentUserFullName(), 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="name"><?php echo getCurrentUserFullName(); ?></div>
                            <div class="role"><?php echo ucfirst(getCurrentUserRole()); ?></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="showLogoutModal()">Logout</button>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="page-content">
