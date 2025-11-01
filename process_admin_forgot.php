<?php
session_start();
require_once __DIR__ . '/php/db.php';

$email = $_POST['email'];
$captcha_input = (int)$_POST['captcha_input'];
$captcha_answer = (int)$_SESSION['captcha_answer'];

if ($captcha_input !== $captcha_answer) {
    // CAPTCHA was wrong. Redirect back.
    header("Location: admin_forgot_password.php?error=wrong_captcha");
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND is_admin = 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    $_SESSION['user_id_to_reset'] = $user['user_id'];
    
    unset($_SESSION['captcha_answer']);
    
    header("Location: admin_reset_password.php");
    exit;
    
} else {
    header("Location: admin_forgot_password.php?error=no_email");
    exit;
}

$stmt->close();
$conn->close();
?>

