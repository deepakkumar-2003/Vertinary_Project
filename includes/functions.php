<?php
// Helper Functions

// Redirect function
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Display message
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';

        $alertClass = '';
        switch($type) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            default:
                $alertClass = 'alert-info';
        }

        echo "<div class='alert {$alertClass} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";

        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Set message
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate unique code
function generateCode($prefix, $length = 6) {
    $number = str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    return strtoupper($prefix) . $number;
}

// Format date for display
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date) || $date == '0000-00-00') {
        return '-';
    }
    return date($format, strtotime($date));
}

// Format datetime for display
function formatDateTime($datetime, $format = DISPLAY_DATETIME_FORMAT) {
    if (empty($datetime) || $datetime == '0000-00-00 00:00:00') {
        return '-';
    }
    return date($format, strtotime($datetime));
}

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Calculate age from date of birth
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today);

    if ($age->y > 0) {
        return $age->y . ' year(s) ' . $age->m . ' month(s)';
    } else {
        return $age->m . ' month(s)';
    }
}

// Upload file
function uploadFile($file, $uploadDir, $allowedTypes = ALLOWED_IMAGE_TYPES) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed size'];
    }

    // Check file type
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = $uploadDir . '/' . $filename;

    // Create directory if not exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $uploadPath];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

// Delete file
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// Get user role badge
function getRoleBadge($role) {
    $badges = [
        'admin' => '<span class="badge bg-danger">Admin</span>',
        'veterinarian' => '<span class="badge bg-primary">Veterinarian</span>',
        'staff' => '<span class="badge bg-info">Staff</span>',
        'farm_owner' => '<span class="badge bg-success">Farm Owner</span>'
    ];
    return $badges[$role] ?? '<span class="badge bg-secondary">' . ucfirst($role) . '</span>';
}

// Get status badge
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge bg-success">Active</span>',
        'inactive' => '<span class="badge bg-secondary">Inactive</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'overdue' => '<span class="badge bg-danger">Overdue</span>',
        'paid' => '<span class="badge bg-success">Paid</span>',
        'approved' => '<span class="badge bg-success">Approved</span>',
        'rejected' => '<span class="badge bg-danger">Rejected</span>',
        'under_review' => '<span class="badge bg-info">Under Review</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

// Pagination
function paginate($totalRecords, $currentPage = 1, $recordsPerPage = RECORDS_PER_PAGE) {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $offset = ($currentPage - 1) * $recordsPerPage;

    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'records_per_page' => $recordsPerPage,
        'offset' => $offset
    ];
}

// Log activity
function logActivity($conn, $userId, $action, $module = null) {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $action, $module, $ipAddress);
    $stmt->execute();
    $stmt->close();
}

// Check if date is overdue
function isOverdue($date) {
    return strtotime($date) < strtotime('today');
}

// Get days until date
function getDaysUntil($date) {
    $targetDate = new DateTime($date);
    $today = new DateTime('today');
    $interval = $today->diff($targetDate);

    if ($targetDate < $today) {
        return -$interval->days;
    }
    return $interval->days;
}
