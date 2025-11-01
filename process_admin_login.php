<?php
session_start();
require_once __DIR__ . '/php/db.php'; // Get your database connection

// Get form data
$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT user_id, first_name, password, is_admin FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {

        if ($user['is_admin'] == 1) {
            
            $_SESSION['admin_user_id'] = $user['user_id'];
            $_SESSION['admin_first_name'] = $user['first_name'];
            header("Location: dashboard.php");
            exit;

        } else {
            header("Location: admin_login.php?error=not_admin");
            exit;
        }

    } else {
        header("Location: admin_login.php?error=invalid_credentials");
        exit;
    }

} else {
    header("Location: admin_login.php?error=invalid_credentials");
    exit;
}

$stmt->close();
$conn->close();
?>
