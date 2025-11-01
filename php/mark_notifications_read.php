<?php
session_start();

include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not authenticated"]);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Notifications marked as read"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error"]);
}

$stmt->close();
$conn->close();
?>
