<?php
session_start();
require_once __DIR__ . '/php/db.php'; // Your database connection

// 1. Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: forgotpassword.php");
    exit();
}

// 2. Get form data
$email = trim($_POST['email']);
$captcha_input = trim($_POST['captcha_input']);
$captcha_answer = $_SESSION['captcha_answer'] ?? null;

// 3. Unset the captcha answer immediately to prevent reuse
unset($_SESSION['captcha_answer']);

// 4. Validate the captcha
if ($captcha_input === null || $captcha_input != $captcha_answer) {
    // Answer was wrong, send back with an error
    header("Location: forgotpassword.php?error=wrong_captcha");
    exit();
}

// 5. Check if the email exists in the database
$sql = "SELECT user_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Email not found, send back with an error
    header("Location: forgotpassword.php?error=no_email");
    exit();
}

// 6. SUCCESS: Email and Captcha are valid.
// Generate a secure, single-use token and store it in the session.
$token = bin2hex(random_bytes(32)); 
$_SESSION['reset_token'] = $token;
$_SESSION['reset_email'] = $email; // Store the email we are resetting
$_SESSION['reset_token_expiry'] = time() + 600; // Token is valid for 10 minutes (600 seconds)

// 7. Redirect to the reset form, passing the token in the URL
header("Location: reset_password.php?token=" . $token);
exit();

?>

