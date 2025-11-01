<?php
include 'db.php';

$sql = "SELECT * FROM schedule WHERE is_deleted = FALSE";

// Execute the query
$result = $conn->query($sql);
$schedules = [];

while ($row = $result->fetch_assoc()) {
    $schedules[] = [
        "schedule_id" => $row["schedule_id"], 

        "day" => $row["day"],
        "type" => $row["type"],
        "location" => $row["location"],
        "frequency" => $row["frequency"],
        "route_formatted" => "Dagupan → " . $row["destination"],
        "departure_formatted" => date("g:iA", strtotime($row["departure_time"])),
        "arrival_formatted" => date("g:iA", strtotime($row["estimated_arrival"])),
        "destination" => $row["destination"],
        "departure_time" => $row["departure_time"],
        "estimated_arrival" => $row["estimated_arrival"]
    ];
}

echo json_encode($schedules);
$conn->close();
?>
