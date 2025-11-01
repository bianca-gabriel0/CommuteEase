<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

$schedule_id = $data["schedule_id"];
$day = $data["day"];
$type = $data["type"];
$location = $data["location"];
$destination = $data["destination"];
$departure = $data["departure"];
$arrival = $data["arrival"];
$frequency = $data["frequency"];
$old_stmt = $conn->prepare("SELECT * FROM schedule WHERE schedule_id = ?");
$old_stmt->bind_param("i", $schedule_id);
$old_stmt->execute();
$old_result = $old_stmt->get_result();
$old_data = $old_result->fetch_assoc();
$old_stmt->close();

if (!$old_data) {
    echo json_encode(["status" => "error", "message" => "Schedule not found"]);
    exit;
}

$stmt = $conn->prepare("UPDATE schedule SET 
                            day = ?, 
                            type = ?, 
                            location = ?, 
                            destination = ?, 
                            departure_time = ?, 
                            estimated_arrival = ?, 
                            frequency = ? 
                        WHERE 
                            schedule_id = ?");
                            
$stmt->bind_param("sssssssi", $day, $type, $location, $destination, $departure, $arrival, $frequency, $schedule_id);

if ($stmt->execute()) {
    
    $changes_list = []; 

    if ($old_data['day'] != $day) {
        $changes_list[] = "Day to '" . htmlspecialchars($day) . "'";
    }
    if ($old_data['type'] != $type) {
        $changes_list[] = "Type to '" . htmlspecialchars($type) . "'";
    }
    if ($old_data['location'] != $location) {
        $changes_list[] = "Location to '" . htmlspecialchars($location) . "'";
    }
    if ($old_data['destination'] != $destination) {
        $changes_list[] = "Destination to '" . htmlspecialchars($destination) . "'";
    }
    
    if ($old_data['departure_time'] != $departure) {
        $changes_list[] = "Departure time to " . date("g:i A", strtotime($departure));
    }
    if ($old_data['estimated_arrival'] != $arrival) {
        $changes_list[] = "Est. arrival to " . date("g:i A", strtotime($arrival));
    }
    if ($old_data['frequency'] != $frequency) {
        $changes_list[] = "Frequency to '" . htmlspecialchars($frequency) . "'";
    }

    if (count($changes_list) > 0) {
    
        $message_header = "Schedule (" . htmlspecialchars($old_data['location']) . " to " . htmlspecialchars($old_data['destination']) . ") was updated: ";
        $notification_message = $message_header . implode(", ", $changes_list) . ".";
        
        $link = "schedule.php?id=" . $schedule_id; 

        $user_stmt = $conn->prepare("SELECT user_id FROM saved_schedules WHERE schedule_id = ?");
        $user_stmt->bind_param("i", $schedule_id);
        $user_stmt->execute();
        $result = $user_stmt->get_result();
        
        $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link_url) VALUES (?, ?, ?)");
        
        $user_id_to_notify = 0; 
        $notify_stmt->bind_param("iss", $user_id_to_notify, $notification_message, $link);

        while ($row = $result->fetch_assoc()) {
            $user_id_to_notify = $row['user_id'];
            $notify_stmt->execute(); 
        }

        $user_stmt->close();
        $notify_stmt->close();
    
    } 
    
    echo json_encode(["status" => "success"]);
    
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>

