<?php
// Application Configuration

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Application Settings
define('APP_NAME', 'Veterinary Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/vasanth_project');

// Directory Paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']);

// Pagination
define('RECORDS_PER_PAGE', 20);

// Session Timeout (in seconds)
define('SESSION_TIMEOUT', 3600); // 1 hour

// Password Settings
define('MIN_PASSWORD_LENGTH', 6);

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd-m-Y');
define('DISPLAY_DATETIME_FORMAT', 'd-m-Y h:i A');

// Include required files
require_once ROOT_PATH . '/config/database.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/session.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();
