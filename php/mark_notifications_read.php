<?php
// Start session to get the user_id
session_start();

// Include database connection
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, send an error
    echo json_encode(["status" => "error", "message" => "Not authenticated"]);
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Prepare the UPDATE statement
// We are marking ALL unread notifications for this user as read
$sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);

// Execute the statement
if ($stmt->execute()) {
    // Send success response
    echo json_encode(["status" => "success", "message" => "Notifications marked as read"]);
} else {
    // Send error response
    echo json_encode(["status" => "error", "message" => "Database error"]);
}

$stmt->close();
$conn->close();
?>
