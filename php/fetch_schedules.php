<?php
include 'db.php';

$result = $conn->query("SELECT * FROM schedule");
$schedules = [];

while ($row = $result->fetch_assoc()) {
    $schedules[] = [
        "day" => $row["day"],
        "type" => $row["type"],
        "location" => $row["location"],
        "route" => "Dagupan → " . $row["destination"],
        "departure" => date("g:iA", strtotime($row["departure_time"])),
        "arrival" => date("g:iA", strtotime($row["estimated_arrival"])),
        "frequency" => $row["frequency"]
    ];
}

echo json_encode($schedules);
$conn->close();
?>
