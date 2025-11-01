<?php
session_start();
require_once __DIR__ . '/php/db.php';

// Security Check:
// Make sure a user is actually in the process of resetting.
if (!isset($_SESSION['user_id_to_reset'])) {
    header("Location: admin_login.php"); // Go to login
    exit;
}

// 1. Get the data
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];
$user_id = $_SESSION['user_id_to_reset'];

// 2. Check if passwords match
if ($password !== $confirmPassword) {
    header("Location: admin_reset_password.php?error=mismatch");
    exit;
}

// 3. Check password strength (same as your signup)
if (strlen($password) < 8 || !preg_match('/\d/', $password)) {
    header("Location: admin_reset_password.php?error=weak");
    exit;
}

// 4. All checks passed. Hash the new password.
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 5. Update the user's password in the database
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$stmt->bind_param("si", $hashed_password, $user_id);

if ($stmt->execute()) {
    // Success!
    // Clean up the session
    unset($_SESSION['user_id_to_reset']);
    
    // Redirect to login with a success message
    header("Location: admin_login.php?message=password_updated");
    exit;
    
} else {
    // Database error
    header("Location: admin_reset_password.php?error=db_error");
    exit;
}

$stmt->close();
$conn->close();
?>
