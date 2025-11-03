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
            
            $info_sql = "SELECT location, destination FROM schedule WHERE schedule_id = ?";
            $info_stmt = $conn->prepare($info_sql);
            $info_stmt->bind_param("i", $schedule_id);
            $info_stmt->execute();
            $info_result = $info_stmt->get_result();
            $schedule_info = $info_result->fetch_assoc();
            
            $location = $schedule_info['location'] ?? 'Unknown Location';
            $destination = $schedule_info['destination'] ?? 'Unknown Destination';
            $info_stmt->close();

            $user_sql = "SELECT user_id FROM saved_schedules WHERE schedule_id = ?";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("i", $schedule_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            
            $notification_message = "A schedule you saved (from $location to $destination) has been removed and is no longer available.";
            
            $notify_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $notify_stmt = $conn->prepare($notify_sql);
            $user_id_to_notify = 0; 
            $notify_stmt->bind_param("is", $user_id_to_notify, $notification_message);

            while ($row = $user_result->fetch_assoc()) {
                $user_id_to_notify = $row['user_id'];
                $notify_stmt->execute(); 
            }

            $user_stmt->close();
            $notify_stmt->close();
            
            echo json_encode(['status' => 'success', 'message' => 'Schedule moved to trash and users notified.']);
        
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
