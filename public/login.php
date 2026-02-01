<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(APP_URL . '/public/dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        setMessage('Please enter both username and password', 'error');
    } else {
        // Check user credentials
        $stmt = $conn->prepare("SELECT id, username, password, full_name, role, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if user is active
            if ($user['status'] !== 'active') {
                setMessage('Your account has been deactivated. Please contact administrator', 'error');
            } else {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    loginUser($user['id'], $user['username'], $user['full_name'], $user['role'], $conn);
                    redirect(APP_URL . '/public/dashboard.php');
                } else {
                    setMessage('Invalid username or password', 'error');
                }
            }
        } else {
            setMessage('Invalid username or password', 'error');
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#2563eb">
    <meta name="description" content="Login to Veterinary Management System">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><?php echo APP_NAME; ?></h1>
                <p>Please login to access the system</p>
            </div>

            <?php displayMessage(); ?>

            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="Enter your username"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
            </form>

            <div class="text-center mt-20">
                <p class="text-muted" style="font-size: var(--font-xs);">
                    Default Login: <strong>admin</strong> / <strong>admin123</strong>
                </p>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>
