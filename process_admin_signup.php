<?php
require_once __DIR__ . '/php/db.php'; // Get your database connection

// Get form data
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

// 1. Check if passwords match
if ($password !== $confirmPassword) {
    header("Location: admin_signup.php?error=password_mismatch");
    exit;
}

// 2. Check if email already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Email already exists
    header("Location: admin_signup.php?error=email_exists");
    exit;
}
$stmt->close();

// 3. Hash the password (This matches your process_signup.php)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 4. Insert the new user
// --- THIS IS THE KEY CHANGE ---
// We are now setting 'is_admin' to 1 (true)
// because this is the ADMIN signup form.
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param("ssss", $firstName, $lastName, $email, $hashed_password);

if ($stmt->execute()) {
    // Success! Redirect to login page with a success message
    header("Location: admin_login.php?message=signup_success");
    exit;
} else {
    // Database error
    header("Location: admin_signup.php?error=db_error");
    exit;
}

$stmt->close();
$conn->close();
?>

