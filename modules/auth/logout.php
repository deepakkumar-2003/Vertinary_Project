<?php
require_once '../../config/config.php';

// Logout user
if (isLoggedIn()) {
    logoutUser($conn);
    setMessage('You have been logged out successfully', 'success');
}

redirect(APP_URL . '/public/login.php');
