<?php
include 'db.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

// Get all the data, including the schedule_id
$schedule_id = $data["schedule_id"];
$day = $data["day"];
$type = $data["type"];
$location = $data["location"];
$destination = $data["destination"];
$departure = $data["departure"];
$arrival = $data["arrival"];
$frequency = $data["frequency"];

// Prepare SQL UPDATE statement
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
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>

