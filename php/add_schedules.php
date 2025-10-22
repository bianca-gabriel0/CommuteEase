<?php
include 'db.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

$day = $data["day"];
$type = $data["type"];
$location = $data["location"];
$destination = $data["destination"];
$departure = $data["departure"];
$arrival = $data["arrival"];
$frequency = $data["frequency"];

// Prepare SQL insert
$stmt = $conn->prepare("INSERT INTO schedule (day, type, location, destination, departure_time, estimated_arrival, frequency) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $day, $type, $location, $destination, $departure, $arrival, $frequency);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>
