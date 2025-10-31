<?php
session_start();
// Set headers for JSON response
header('Content-Type: application/json');

// Check for authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated. Please log in again.']);
    exit();
}

// Include database connection (uses the $conn object from your db.php)
include 'php/db.php'; 
$current_user_id = $_SESSION['user_id'];

// Get JSON input from the request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if data was received correctly
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data format received.']);
    exit();
}

// Sanitize and assign variables
$firstName = trim($data['firstName'] ?? '');
$lastName = trim($data['lastName'] ?? '');
$newPassword = $data['newPassword'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';
$currentPassword = $data['currentPassword'] ?? '';

// --- 1. Validation Checks ---
// Removed: Email validation is no longer necessary as it cannot be changed.
if (empty($firstName) || empty($lastName)) {
    echo json_encode(['success' => false, 'message' => 'First Name and Last Name are required.']);
    exit();
}
if (empty($currentPassword)) {
    echo json_encode(['success' => false, 'message' => 'Current Password is required to save changes.']);
    exit();
}

// --- NEW PASSWORD VALIDATION ---
if (!empty($newPassword)) {
    // Check 1: Minimum length of 8 characters
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
        exit();
    }
    // Check 2: Contains at least one number
    if (!preg_match('/\d/', $newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain at least one number.']);
        exit();
    }
    // Check 3: Passwords match
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New and Confirmation passwords do not match.']);
        exit();
    }
}
// --- END NEW PASSWORD VALIDATION ---


// --- 2. Security Check: Verify Current Password and Fetch Hash ---
// We fetch the hash from the 'password' column (based on your table)
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
// Get hash from 'password' column
$stored_hash = $user_data['password'];
// Store email for session refresh later
$current_db_email = $user_data['email'];


// CRITICAL STEP: Check if the current password matches the stored hash
if (!password_verify($currentPassword, $stored_hash)) {
    echo json_encode(['success' => false, 'message' => 'Incorrect current password. Changes not saved.']);
    exit();
}

// --- 2.5. Check if new password is the same as the old one (Only runs if a new password was provided) ---
if (!empty($newPassword)) {
    if (password_verify($newPassword, $stored_hash)) {
        echo json_encode(['success' => false, 'message' => 'The new password must be different from your current password.']);
        exit();
    }
}
// --- END PASSWORD CHECKS ---


// --- 3. Construct Dynamic Update Query ---
$update_parts = [];
$bind_types = "";
$bind_values = [];

// Name updates
$update_parts[] = "first_name = ?";
$bind_types .= "s";
$bind_values[] = $firstName;

$update_parts[] = "last_name = ?";
$bind_types .= "s";
$bind_values[] = $lastName;

// Password update is only included if a new one was provided
if (!empty($newPassword)) {
    // Hash the new password securely
    $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
    // Use the 'password' column name
    $update_parts[] = "password = ?";
    $bind_types .= "s";
    $bind_values[] = $hashed_password;
}

// Check for actual changes compared to current session data (only name and password matter now)
if ($firstName == $_SESSION['first_name'] && $lastName == $_SESSION['last_name'] && empty($newPassword)) {
    echo json_encode(['success' => true, 'message' => 'No new changes detected.']);
    exit();
}

// If no fields were selected to update (shouldn't happen with required name fields, but as a safeguard)
if (empty($update_parts)) {
    echo json_encode(['success' => true, 'message' => 'No fields were updated.']);
    exit();
}


// --- 4. Execute Update ---
$sql_update = "UPDATE users SET " . implode(", ", $update_parts) . " WHERE user_id = ?";

if ($stmt_update = $conn->prepare($sql_update)) {
    // Add user_id to the bind values list (at the end)
    $bind_types .= "i";
    $bind_values[] = $current_user_id;

    // Dynamically bind parameters using the splat operator (...)
    $stmt_update->bind_param($bind_types, ...$bind_values);

    if ($stmt_update->execute()) {
        // Update session to reflect new name (email remains the same)
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        // Ensure email in session is the correct current email
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
