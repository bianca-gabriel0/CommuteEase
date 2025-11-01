<?php
session_start();
require_once __DIR__ . '/php/db.php'; 

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Invalid request method.");
}

$token = $_POST['token'] ?? null;
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

$is_valid_token = $token &&
                  isset($_SESSION['reset_token']) &&
                  $_SESSION['reset_token'] == $token &&
                  isset($_SESSION['reset_token_expiry']) &&
                  time() < $_SESSION['reset_token_expiry'];

if (!$is_valid_token) {
    die("Invalid or expired password reset token. Please try again. <a href='forgotpassword.php'>Go back</a>");
}

if ($new_password !== $confirm_password) {
    die("Passwords do not match. <a href='javascript:history.back()'>Go back</a>");
}
if (strlen($new_password) < 8 || !preg_match('/\d/', $new_password)) {
    die("Password must be at least 8 characters and contain a number. <a href='javascript:history.back()'>Go back</a>");
}

$email = $_SESSION['reset_email'];
$new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $new_hashed_password, $email);
$stmt->execute();

unset($_SESSION['reset_token']);
unset($_SESSION['reset_email']);
unset($_SESSION['reset_token_expiry']);

header("Location: login.php?message=password_reset_success");
exit();

?>
