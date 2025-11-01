<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated. Please log in again.']);
    exit();
}

include 'php/db.php'; 
$current_user_id = $_SESSION['user_id'];

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data format received.']);
    exit();
}

$firstName = trim($data['firstName'] ?? '');
$lastName = trim($data['lastName'] ?? '');
$newPassword = $data['newPassword'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';
$currentPassword = $data['currentPassword'] ?? '';

if (empty($firstName) || empty($lastName)) {
    echo json_encode(['success' => false, 'message' => 'First Name and Last Name are required.']);
    exit();
}
if (empty($currentPassword)) {
    echo json_encode(['success' => false, 'message' => 'Current Password is required to save changes.']);
    exit();
}

if (!empty($newPassword)) {
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
        exit();
    }
    if (!preg_match('/\d/', $newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain at least one number.']);
        exit();
    }
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New and Confirmation passwords do not match.']);
        exit();
    }
}

$sql_fetch_hash = "SELECT password, email FROM users WHERE user_id = ?";
$stmt_fetch_hash = $conn->prepare($sql_fetch_hash);
if (!$stmt_fetch_hash) {
    error_log("Error preparing hash fetch statement: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again later. (Code 500)']);
    exit();
}

$stmt_fetch_hash->bind_param("i", $current_user_id);
$stmt_fetch_hash->execute();
$result = $stmt_fetch_hash->get_result();
$stmt_fetch_hash->close();

if ($result->num_rows !== 1) {
    echo json_encode(['success' => false, 'message' => 'Account not found.']);
    exit();
}
$user_data = $result->fetch_assoc();
$stored_hash = $user_data['password'];
$current_db_email = $user_data['email'];

if (!password_verify($currentPassword, $stored_hash)) {
    echo json_encode(['success' => false, 'message' => 'Incorrect current password. Changes not saved.']);
    exit();
}

if (!empty($newPassword)) {
    if (password_verify($newPassword, $stored_hash)) {
        echo json_encode(['success' => false, 'message' => 'The new password must be different from your current password.']);
        exit();
    }
}

$update_parts = [];
$bind_types = "";
$bind_values = [];

$update_parts[] = "first_name = ?";
$bind_types .= "s";
$bind_values[] = $firstName;

$update_parts[] = "last_name = ?";
$bind_types .= "s";
$bind_values[] = $lastName;

if (!empty($newPassword)) {
    $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
    $update_parts[] = "password = ?";
    $bind_types .= "s";
    $bind_values[] = $hashed_password;
}

if ($firstName == $_SESSION['first_name'] && $lastName == $_SESSION['last_name'] && empty($newPassword)) {
    echo json_encode(['success' => true, 'message' => 'No new changes detected.']);
    exit();
}

if (empty($update_parts)) {
    echo json_encode(['success' => true, 'message' => 'No fields were updated.']);
    exit();
}

$sql_update = "UPDATE users SET " . implode(", ", $update_parts) . " WHERE user_id = ?";

if ($stmt_update = $conn->prepare($sql_update)) {
    $bind_types .= "i";
    $bind_values[] = $current_user_id;

    $stmt_update->bind_param($bind_types, ...$bind_values);

    if ($stmt_update->execute()) {
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['email'] = $current_db_email; 
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } else {
        error_log("Error executing profile update: " . $stmt_update->error);
        echo json_encode(['success' => false, 'message' => 'Database update failed.']);
    }
    $stmt_update->close();
} else {
    error_log("Error preparing update statement: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Server error during statement preparation.']);
}

$conn->close();
?>
