<?php
include 'db.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

// Get all the new data, including the schedule_id
$schedule_id = $data["schedule_id"];
$day = $data["day"];
$type = $data["type"];
$location = $data["location"];
$destination = $data["destination"];
$departure = $data["departure"];
$arrival = $data["arrival"];
$frequency = $data["frequency"];


// --- NEW: STEP 1 - GET OLD SCHEDULE DATA ---
// We need to see what the data was *before* we update it
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

// --- STEP 2 - PREPARE AND EXECUTE THE UPDATE ---
// (This is your original update query)
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
                            
// Bind parameters (7 strings, 1 integer)
$stmt->bind_param("sssssssi", $day, $type, $location, $destination, $departure, $arrival, $frequency, $schedule_id);

if ($stmt->execute()) {
    
    // --- NEW: STEP 3 - COMPARE OLD AND NEW DATA ---
    
    $changes_list = []; // An array to hold all the changes

    // We use the database column names from $old_data
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
    
    // For times, let's compare them directly. We'll format them nicely for the message.
    if ($old_data['departure_time'] != $departure) {
        $changes_list[] = "Departure time to " . date("g:i A", strtotime($departure));
    }
    if ($old_data['estimated_arrival'] != $arrival) {
        $changes_list[] = "Est. arrival to " . date("g:i A", strtotime($arrival));
    }
    if ($old_data['frequency'] != $frequency) {
        $changes_list[] = "Frequency to '" . htmlspecialchars($frequency) . "'";
    }

    // --- NEW: STEP 4 - BUILD AND SEND NOTIFICATIONS (ONLY IF CHANGES EXIST) ---
    
    // Only send a notification if something actually changed
    if (count($changes_list) > 0) {
    
        // 1. Build the new detailed message
        // Use the *old* location/destination as a reference, in case they changed
        $message_header = "Schedule (" . htmlspecialchars($old_data['location']) . " to " . htmlspecialchars($old_data['destination']) . ") was updated: ";
        $notification_message = $message_header . implode(", ", $changes_list) . ".";
        
        $link = "schedule.php?id=" . $schedule_id; // Or whatever your view page is

        // 2. Find all users who saved this schedule
        $user_stmt = $conn->prepare("SELECT user_id FROM saved_schedules WHERE schedule_id = ?");
        $user_stmt->bind_param("i", $schedule_id);
        $user_stmt->execute();
        $result = $user_stmt->get_result();
        
        // 3. Prepare the notification INSERT statement *once*
        $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link_url) VALUES (?, ?, ?)");
        
        $user_id_to_notify = 0; // We'll update this in the loop
        $notify_stmt->bind_param("iss", $user_id_to_notify, $notification_message, $link);

        // 4. Loop through all found users and insert a notification for each
        while ($row = $result->fetch_assoc()) {
            $user_id_to_notify = $row['user_id']; // This updates the bound variable
            $notify_stmt->execute(); // Insert the notification
        }

        // 5. Close the new statements
        $user_stmt->close();
        $notify_stmt->close();
    
    } // End if (count($changes_list) > 0)
    
    // --- NOTIFICATION LOGIC ENDS HERE ---

    // Finally, send the success status back to the frontend
    echo json_encode(["status" => "success"]);
    
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>

