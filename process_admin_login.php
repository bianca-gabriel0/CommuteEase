<?php
session_start();
require_once __DIR__ . '/php/db.php'; // Get your database connection

// Get form data
$email = $_POST['email'];
$password = $_POST['password'];

// 1. Find the user by email
$stmt = $conn->prepare("SELECT user_id, first_name, password, is_admin FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // 2. Verify the password
    // --- THIS IS AN ASSUMPTION ---
    // I am assuming you use password_verify(). If not, change this.
    if (password_verify($password, $user['password'])) {

        // 3. CHECK IF THEY ARE AN ADMIN
        // --- THIS IS A CRITICAL ASSUMPTION ---
        // I am assuming you have an 'is_admin' column and it is set to 1.
        // Change 'is_admin' if your column is named differently.
        if ($user['is_admin'] == 1) {
            
            // Success! Set session variables and redirect to dashboard
            $_SESSION['admin_user_id'] = $user['user_id'];
            $_SESSION['admin_first_name'] = $user['first_name'];
            header("Location: dashboard.php");
            exit;

        } else {
            // Valid user, but NOT an admin
            header("Location: admin_login.php?error=not_admin");
            exit;
        }

    } else {
        // Password mismatch
        header("Location: admin_login.php?error=invalid_credentials");
        exit;
    }

} else {
    // Email not found
    header("Location: admin_login.php?error=invalid_credentials");
    exit;
}

$stmt->close();
$conn->close();
?>
