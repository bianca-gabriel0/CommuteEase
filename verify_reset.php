<?php
session_start();
require_once __DIR__ . '/php/db.php'; 

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: forgotpassword.php");
    exit();
}

$email = trim($_POST['email']);
$captcha_input = trim($_POST['captcha_input']);
$captcha_answer = $_SESSION['captcha_answer'] ?? null;

unset($_SESSION['captcha_answer']);

if ($captcha_input === null || $captcha_input != $captcha_answer) {
    header("Location: forgotpassword.php?error=wrong_captcha");
    exit();
}

$sql = "SELECT user_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: forgotpassword.php?error=no_email");
    exit();
}

$token = bin2hex(random_bytes(32)); 
$_SESSION['reset_token'] = $token;
$_SESSION['reset_email'] = $email; 
$_SESSION['reset_token_expiry'] = time() + 600; 

header("Location: reset_password.php?token=" . $token);
exit();

?>

