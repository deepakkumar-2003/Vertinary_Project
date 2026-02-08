<?php
/**
 * Admin User Setup Script
 * This script creates or updates the admin user with proper password hashing
 * Run this once to set up the default admin account
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';

// Default admin credentials
$admin_username = 'admin';
$admin_password = 'admin123'; // Plain text password
$admin_fullname = 'System Administrator';
$admin_email = 'admin@vetclinic.com';
$admin_role = 'admin';
$admin_status = 'active';

// Hash the password using bcrypt
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Veterinary Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 10px;
        }
        .success {
            color: #22c55e;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .info {
            background: #e0f2fe;
            padding: 15px;
            border-left: 4px solid #0284c7;
            margin: 20px 0;
        }
        .warning {
            background: #fef3c7;
            padding: 15px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
            font-weight: bold;
        }
        .credentials {
            background: #f0fdf4;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .credentials strong {
            color: #16a34a;
        }
        a {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        a:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔧 Admin User Setup</h2>

        <?php
        try {
            // Test database connection
            if (!$conn) {
                throw new Exception("Database connection failed. Please check your database configuration.");
            }

            // Check if admin user already exists
            $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }

            $stmt->bind_param("s", $admin_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update existing admin user
                $user = $result->fetch_assoc();

                $update_stmt = $conn->prepare("UPDATE users SET password = ?, full_name = ?, email = ?, role = ?, status = ? WHERE id = ?");
                if (!$update_stmt) {
                    throw new Exception("Failed to prepare update statement: " . $conn->error);
                }

                $update_stmt->bind_param("sssssi", $hashed_password, $admin_fullname, $admin_email, $admin_role, $admin_status, $user['id']);

                if ($update_stmt->execute()) {
                    echo "<p class='success'>✓ Admin user UPDATED successfully!</p>";
                    echo "<div class='info'>";
                    echo "<p><strong>Note:</strong> An existing admin user was found and has been updated.</p>";
                    echo "</div>";
                } else {
                    throw new Exception("Error updating admin user: " . $conn->error);
                }
                $update_stmt->close();

            } else {
                // Insert new admin user
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                if (!$insert_stmt) {
                    throw new Exception("Failed to prepare insert statement: " . $conn->error);
                }

                $insert_stmt->bind_param("ssssss", $admin_username, $hashed_password, $admin_fullname, $admin_email, $admin_role, $admin_status);

                if ($insert_stmt->execute()) {
                    echo "<p class='success'>✓ Admin user CREATED successfully!</p>";
                    echo "<div class='info'>";
                    echo "<p><strong>Note:</strong> A new admin user has been created.</p>";
                    echo "</div>";
                } else {
                    throw new Exception("Error creating admin user: " . $conn->error);
                }
                $insert_stmt->close();
            }

            // Display credentials
            echo "<div class='credentials'>";
            echo "<h3>📋 Login Credentials:</h3>";
            echo "<p><strong>Username:</strong> " . htmlspecialchars($admin_username) . "</p>";
            echo "<p><strong>Password:</strong> " . htmlspecialchars($admin_password) . "</p>";
            echo "<p><strong>Role:</strong> " . htmlspecialchars($admin_role) . "</p>";
            echo "<p><strong>Status:</strong> " . htmlspecialchars($admin_status) . "</p>";
            echo "</div>";

            // Verify the password works
            $verify_stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
            $verify_stmt->bind_param("s", $admin_username);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $verify_user = $verify_result->fetch_assoc();

            if (password_verify($admin_password, $verify_user['password'])) {
                echo "<p class='success'>✓ Password verification test: PASSED</p>";
            } else {
                echo "<p class='error'>✗ Password verification test: FAILED</p>";
            }
            $verify_stmt->close();

            $stmt->close();
            $conn->close();

            echo "<br>";
            echo "<a href='login.php'>➜ Go to Login Page</a>";

            echo "<div class='warning'>";
            echo "⚠️ SECURITY WARNING: Delete this file (setup_admin.php) after setting up the admin user!";
            echo "</div>";

        } catch (Exception $e) {
            echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<div class='info'>";
            echo "<h4>Troubleshooting:</h4>";
            echo "<ul>";
            echo "<li>Verify MySQL is running in XAMPP Control Panel</li>";
            echo "<li>Check database credentials in <code>config/database.php</code></li>";
            echo "<li>Ensure database '<strong>vet_management_system</strong>' exists</li>";
            echo "<li>Ensure the '<strong>users</strong>' table exists in the database</li>";
            echo "</ul>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
