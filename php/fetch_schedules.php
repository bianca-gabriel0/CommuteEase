<?php
include 'db.php';

// UPDATED: Make sure to select schedule_id
$result = $conn->query("SELECT * FROM schedule");
$schedules = [];

while ($row = $result->fetch_assoc()) {
    $schedules[] = [
        // --- THIS IS CRITICAL ---
        "schedule_id" => $row["schedule_id"], 

        // --- Original data ---
        "day" => $row["day"],
        "type" => $row["type"],
        "location" => $row["location"],
        "frequency" => $row["frequency"],
        
        // --- Formatted data (for table display) ---
        "route_formatted" => "Dagupan → " . $row["destination"],
        "departure_formatted" => date("g:iA", strtotime($row["departure_time"])),
        "arrival_formatted" => date("g:iA", strtotime($row["estimated_arrival"])),

        // --- Raw data (for populating the edit form) ---
        "destination" => $row["destination"],
        "departure_time" => $row["departure_time"],
        "estimated_arrival" => $row["estimated_arrival"]
    ];
}

echo json_encode($schedules);
$conn->close();
?>