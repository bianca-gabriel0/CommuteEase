<?php
session_start();
require_once __DIR__ . '/php/db.php'; // Your database connection

// 1. Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Invalid request method.");
}

// 2. Get all POST data
$token = $_POST['token'] ?? null;
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// 3. Validate the token AGAIN (Final security check)
$is_valid_token = $token &&
                  isset($_SESSION['reset_token']) &&
                  $_SESSION['reset_token'] == $token &&
                  isset($_SESSION['reset_token_expiry']) &&
                  time() < $_SESSION['reset_token_expiry'];

if (!$is_valid_token) {
    die("Invalid or expired password reset token. Please try again. <a href='forgotpassword.php'>Go back</a>");
}

// 4. Validate the new password
if ($new_password !== $confirm_password) {
    die("Passwords do not match. <a href='javascript:history.back()'>Go back</a>");
}
if (strlen($new_password) < 8 || !preg_match('/\d/', $new_password)) {
    die("Password must be at least 8 characters and contain a number. <a href='javascript:history.back()'>Go back</a>");
}

// 5. ALL CHECKS PASSED. Update the database.
$email = $_SESSION['reset_email'];
$new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $new_hashed_password, $email);
$stmt->execute();

// 6. Clean up the session to invalidate the token
unset($_SESSION['reset_token']);
unset($_SESSION['reset_email']);
unset($_SESSION['reset_token_expiry']);

// 7. Redirect to login page with a success message
header("Location: login.php?message=password_reset_success");
exit();

?>
