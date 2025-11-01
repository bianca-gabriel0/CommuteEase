<?php
header('Content-Type: application/json');

include 'db.php'; 

$data = json_decode(file_get_contents("php://input"), true);
$schedule_id = $data['schedule_id'] ?? null;

if (empty($schedule_id) || !is_numeric($schedule_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid schedule ID provided for deletion.']);
    exit(); 
}

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

try {
    $sql = "UPDATE schedule SET is_deleted = TRUE WHERE schedule_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Schedule moved to trash successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Schedule ID not found or already in trash.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to execute query: ' . $stmt->error]);
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();

?>
