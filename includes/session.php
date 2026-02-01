<?php
// Session Management

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        setMessage('Please login to access this page', 'error');
        redirect(APP_URL . '/public/login.php');
    }
}

// Check user role
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    return $_SESSION['role'] === $role;
}

// Check if user has any of the specified roles
function hasAnyRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    return in_array($_SESSION['role'], $roles);
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        setMessage('You do not have permission to access this page', 'error');
        redirect(APP_URL . '/public/dashboard.php');
    }
}

// Require any of the specified roles
function requireAnyRole($roles) {
    requireLogin();
    if (!hasAnyRole($roles)) {
        setMessage('You do not have permission to access this page', 'error');
        redirect(APP_URL . '/public/dashboard.php');
    }
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

// Get current user role
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Get current user full name
function getCurrentUserFullName() {
    return $_SESSION['full_name'] ?? 'User';
}

// Login user
function loginUser($userId, $username, $fullName, $role, $conn) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['full_name'] = $fullName;
    $_SESSION['role'] = $role;
    $_SESSION['login_time'] = time();

    // Update last login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // Log activity
    logActivity($conn, $userId, 'User logged in', 'auth');
}

// Logout user
function logoutUser($conn) {
    if (isLoggedIn()) {
        // Log activity
        logActivity($conn, getCurrentUserId(), 'User logged out', 'auth');

        // Clear session
        session_unset();
        session_destroy();
    }
}

// Check session timeout
function checkSessionTimeout() {
    if (isLoggedIn() && isset($_SESSION['login_time'])) {
        $elapsed = time() - $_SESSION['login_time'];
        if ($elapsed > SESSION_TIMEOUT) {
            global $conn;
            logoutUser($conn);
            setMessage('Your session has expired. Please login again', 'warning');
            redirect(APP_URL . '/public/login.php');
        }
    }
}

// Update session activity
function updateSessionActivity() {
    if (isLoggedIn()) {
        $_SESSION['login_time'] = time();
    }
}

// Auto-update session activity on each page load
if (isLoggedIn()) {
    checkSessionTimeout();
    updateSessionActivity();
}
