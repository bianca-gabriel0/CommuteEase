<?php
session_start();
require_once __DIR__ . '/php/db.php';

// 1. Get data from form and session
$email = $_POST['email'];
$captcha_input = (int)$_POST['captcha_input'];
$captcha_answer = (int)$_SESSION['captcha_answer'];

// 2. Check the CAPTCHA answer first
if ($captcha_input !== $captcha_answer) {
    // CAPTCHA was wrong. Redirect back.
    header("Location: admin_forgot_password.php?error=wrong_captcha");
    exit;
}

// 3. CAPTCHA was correct. Now check the email.
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND is_admin = 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // Success! Admin account found.
    $user = $result->fetch_assoc();
    
    // Store the user ID in the session so we know who is resetting
    $_SESSION['user_id_to_reset'] = $user['user_id'];
    
    // Unset the captcha answer so it can't be reused
    unset($_SESSION['captcha_answer']);
    
    // Redirect to the NEW password reset page
    header("Location: admin_reset_password.php");
    exit;
    
} else {
    // No admin account found with that email
    header("Location: admin_forgot_password.php?error=no_email");
    exit;
}

$stmt->close();
$conn->close();
?>

