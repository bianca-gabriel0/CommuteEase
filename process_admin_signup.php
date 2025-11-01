<?php
require_once __DIR__ . '/php/db.php'; 

$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

if ($password !== $confirmPassword) {
    header("Location: admin_signup.php?error=password_mismatch");
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: admin_signup.php?error=email_exists");
    exit;
}
$stmt->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param("ssss", $firstName, $lastName, $email, $hashed_password);

if ($stmt->execute()) {
    header("Location: admin_login.php?message=signup_success");
    exit;
} else {
    header("Location: admin_signup.php?error=db_error");
    exit;
}

$stmt->close();
$conn->close();
?>

