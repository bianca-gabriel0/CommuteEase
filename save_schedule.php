<?php

session_start();

include 'php/db.php'; 

if (isset($_SESSION['user_id']) && $_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $schedule_id = $_POST['schedule_id'];
    
    $redirect_status = "exists"; 

    if (!empty($schedule_id)) {
        
        $sql = "INSERT IGNORE INTO saved_schedules (user_id, schedule_id) VALUES (?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $schedule_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $redirect_status = "saved"; 
        }
        
        $stmt->close();
        $conn->close();
    }
    
    header("Location: schedule-main.php?status=" . $redirect_status);
    exit;

} else {
    header("Location: schedule-main.php");
    exit; 
}

?>

