<?php
session_start();
require_once __DIR__ . '/php/db.php';

if (!isset($_SESSION['user_id_to_reset'])) {
    header("Location: admin_login.php"); 
    exit;
}

$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];
$user_id = $_SESSION['user_id_to_reset'];

if ($password !== $confirmPassword) {
    header("Location: admin_reset_password.php?error=mismatch");
    exit;
}

if (strlen($password) < 8 || !preg_match('/\d/', $password)) {
    header("Location: admin_reset_password.php?error=weak");
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$stmt->bind_param("si", $hashed_password, $user_id);

if ($stmt->execute()) {
    unset($_SESSION['user_id_to_reset']);
    header("Location: admin_login.php?message=password_updated");
    exit;
    
} else {
    header("Location: admin_reset_password.php?error=db_error");
    exit;
}

$stmt->close();
$conn->close();
?>
