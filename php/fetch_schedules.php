<?php
// NOTE: Assuming db.php initializes $conn using MySQLi or similar.
include 'db.php';

// 🐛 FIX: Modified the SELECT query to include WHERE is_deleted = FALSE
// This ensures that schedules marked as "trashed" are no longer displayed on the main dashboard.
$sql = "SELECT * FROM schedule WHERE is_deleted = FALSE";

// Execute the query
$result = $conn->query($sql);
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
        // Note: Check that 'destination', 'departure_time', and 'estimated_arrival' are correct column names.
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
